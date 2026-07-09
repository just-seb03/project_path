<?php
session_start();
require_once "../config/db.php";

/**
 * login.php solo guarda $_SESSION['rol'] (nombre de la tabla, ej "estudiantes")
 * y $_SESSION['correo']. No hay id_usuario en sesión, así que buscamos
 * al estudiante por correo cada vez.
 */
if (!isset($_SESSION["correo"]) || $_SESSION["rol"] !== "Estudiantes") {
    header("Location: ../public/login.php?error=1");
    exit();
}

$correo_sesion = $_SESSION["correo"];

// Estudiante + carrera + universidad + práctica activa + tutor + encargado
// (LEFT JOIN desde practicas hacia abajo porque un estudiante nuevo puede
// aún no tener práctica asignada).
$sql = "SELECT
            e.id_estudiante, e.nombres, e.apellidos, e.correo, e.rut,
            c.nombre AS carrera, uni.nombre AS universidad,
            p.id_practica, p.fecha_inicio_practica, p.fecha_termino_practica,
            est.nombre AS estado_practica,
            emp.nombre AS empresa,
            t.id_tutor, t.nombres AS tutor_nombres, t.apellidos AS tutor_apellidos, t.cargo AS tutor_cargo,
            enc.id_encargado, enc.nombres AS encargado_nombres, enc.apellidos AS encargado_apellidos
        FROM estudiantes e
        JOIN carreras c ON c.id_career = e.id_carrera
        JOIN sedes s ON s.id_sede = c.id_sede
        JOIN universidades uni ON uni.id_universidad = s.id_university
        LEFT JOIN practicas p ON p.id_estudiante = e.id_estudiante
        LEFT JOIN estados est ON est.id_estado = p.id_estado
        LEFT JOIN tutores t ON t.id_tutor = p.id_tutor
        LEFT JOIN empresa emp ON emp.id_empresa = t.id_empresa
        LEFT JOIN encargados enc ON enc.id_encargado = p.id_encargado
        WHERE e.correo = :correo
        ORDER BY p.id_practica DESC
        LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute(["correo" => $correo_sesion]);
$estudiante = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$id_practica = $estudiante["id_practica"] ?? null;

// Historial de informes (usa la tabla `documentos` extendida con
// id_practica, ver extension_documentos_v2.sql)
$informes = [];
if ($id_practica) {
    $stmt = $pdo->prepare(
        "SELECT id_documentos AS id_informe, tipo_informe, nombre_archivo, fecha_subida, estado, comentario_revisor
         FROM documentos
         WHERE id_practica = :id_practica
         ORDER BY fecha_subida DESC",
    );
    $stmt->execute(["id_practica" => $id_practica]);
    $informes = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Mensajes con tutor y con encargado (requiere la tabla `mensajes`)
$mensajes_tutor = [];
$mensajes_encargado = [];
if ($id_practica) {
    $sql_msj = "SELECT emisor_rol, emisor_correo, contenido, fecha_envio
                FROM mensajes
                WHERE id_practica = :id_practica AND destinatario_rol = :rol
                ORDER BY fecha_envio ASC";

    $stmt = $pdo->prepare($sql_msj);
    $stmt->execute(["id_practica" => $id_practica, "rol" => "tutor"]);
    $mensajes_tutor = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare($sql_msj);
    $stmt->execute(["id_practica" => $id_practica, "rol" => "encargado"]);
    $mensajes_encargado = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function iniciales($nombres, $apellidos) {
    $n = mb_substr(trim((string)$nombres), 0, 1);
    $a = mb_substr(trim((string)$apellidos), 0, 1);
    return mb_strtoupper($n . $a);
}

include "../templates/header.php";
?>

<style>
    /* ---- Tokens de la vista: carpeta de trámites / dossier de práctica ---- */
    :root{
        --ink:#1B2A38;
        --ink-soft:#4A5A68;
        --paper:#EFE9DC;
        --paper-raised:#FBF8F2;
        --folder:#C99B5F;
        --folder-dark:#9C7238;
        --stamp-green:#3F6C4E;
        --stamp-amber:#A6752C;
        --stamp-red:#9C3B34;
        --line:#D8CDB8;
        --font-display: 'Source Serif 4', Georgia, serif;
        --font-mono: 'IBM Plex Mono', 'Courier New', monospace;
        --font-body: 'Source Sans 3', Arial, sans-serif;
    }

    body > .main-header,
    body > #bg-video,
    body > .bg-decoration{
        display: none !important;
    }
    body{
        background: var(--paper) !important;
        margin: 0;
    }
    body > main#app-content{
        padding: 0;
        display: block;
    }

    .practica-wrapper{
        background: var(--paper);
        min-height: 100vh;
        display:flex;
        color: var(--ink);
        font-family: var(--font-body);
    }

    .practica-sidebar{
        width: 250px;
        flex-shrink:0;
        background: var(--ink);
        color: var(--paper-raised);
        padding: 28px 0;
        display:flex;
        flex-direction:column;
        gap: 4px;
    }
    .sidebar-perfil{
        padding: 0 22px 22px;
        border-bottom: 1px solid rgba(255,255,255,.12);
        margin-bottom: 18px;
    }
    .sidebar-avatar{
        width:48px;height:48px;border-radius:6px;
        background: var(--folder);
        color: var(--ink);
        font-family: var(--font-mono);
        font-weight:700;
        display:flex;align-items:center;justify-content:center;
        font-size:16px;
        margin-bottom:10px;
    }
    .sidebar-perfil h3{ font-family: var(--font-display); font-size:17px; margin:0 0 2px; }
    .sidebar-perfil span{ font-size:12px; color:#B9C2CA; font-family: var(--font-mono); }

    .sidebar-tab{
        position:relative;
        padding: 12px 22px 12px 28px;
        font-family: var(--font-mono);
        font-size: 13px;
        letter-spacing:.03em;
        color:#C7CED4;
        text-decoration:none;
        border-left: 3px solid transparent;
        cursor:pointer;
        display:flex; align-items:center; gap:10px;
    }
    .sidebar-tab svg{ width:16px; height:16px; flex-shrink:0; }
    .sidebar-tab:hover{ background: rgba(255,255,255,.05); color:#fff; }
    .sidebar-tab.active{
        color:#fff;
        border-left-color: var(--folder);
        background: rgba(255,255,255,.06);
    }
    .sidebar-tab.active::after{
        content:"";
        position:absolute; right:-1px; top:0; bottom:0; width:6px;
        background: var(--paper);
        border-radius: 6px 0 0 6px;
    }
    .sidebar-logout{
        margin-top:auto;
        padding: 12px 22px 0 28px;
        font-family: var(--font-mono);
        font-size:12px;
    }
    .sidebar-logout a{ color:#E4B2AC; text-decoration:none; }

    .practica-main{ flex:1; padding: 34px 44px; max-width: 1100px; }

    .dashboard-main-header{
        margin-bottom: 26px;
        display: flex;
        flex-direction: column;
        gap: 8px;
        position: relative;
    }
    .eyebrow{
        font-family: var(--font-mono);
        font-size:11px; letter-spacing:.12em; text-transform:uppercase;
        color: var(--folder-dark);
        margin-bottom:2px;
    }
    .dashboard-main-header h1{
        font-family: var(--font-display);
        font-size: 30px;
        margin: 0;
        text-align: center;
        flex: 1;
    }
    .estado-practica{
        display:inline-flex; align-items:center; gap:6px;
        font-family: var(--font-mono); font-size:12px;
        color: var(--ink-soft);
        align-self: flex-end;
        margin-top: 4px;
    }
    .estado-dot{ width:8px;height:8px;border-radius:50%; background: var(--stamp-green); }

    .panel{
        background: var(--paper-raised);
        border: 1px solid var(--line);
        border-radius: 4px;
        margin-bottom: 26px;
    }
    .panel-head{
        padding: 16px 22px;
        border-bottom: 1px solid var(--line);
        display:flex; align-items:center; justify-content:space-between;
    }
    .panel-head h2{ font-family: var(--font-display); font-size:19px; margin:0; }
    .panel-body{ padding: 22px; }

    .dropzone{
        border: 2px dashed var(--folder);
        border-radius: 4px;
        padding: 30px 20px;
        text-align:center;
        cursor:pointer;
        transition: background .15s ease, border-color .15s ease;
        background: repeating-linear-gradient(135deg, transparent, transparent 10px, rgba(201,155,95,.05) 10px, rgba(201,155,95,.05) 20px);
    }
    .dropzone.dragover{ background: rgba(201,155,95,.15); border-color: var(--folder-dark); }
    .dropzone p{ margin: 6px 0 0; font-size:13px; color: var(--ink-soft); }
    .dropzone strong{ font-family: var(--font-mono); }
    .dropzone .filename{
        margin-top:10px; font-family: var(--font-mono); font-size:12px;
        color: var(--stamp-green); display:none;
    }

    .form-row{ display:flex; gap:14px; margin-top:16px; flex-wrap:wrap; }
    .form-row .field{ flex:1; min-width:200px; }
    .field label{
        display:block; font-family: var(--font-mono); font-size:11px;
        text-transform:uppercase; letter-spacing:.06em; color: var(--ink-soft);
        margin-bottom:5px;
    }
    .field select, .field input[type=text], .field textarea{
        width:100%; padding:9px 10px; border:1px solid var(--line);
        border-radius:3px; background:#fff; font-family: var(--font-body); font-size:14px;
        box-sizing:border-box;
    }
    .btn-stamp{
        margin-top:18px;
        font-family: var(--font-mono);
        font-size:13px; letter-spacing:.04em; text-transform:uppercase;
        background: var(--ink); color:#fff; border:none;
        padding: 11px 22px; border-radius:3px; cursor:pointer;
    }
    .btn-stamp:hover{ background:#0F1A22; }
    .btn-stamp:disabled{ opacity:.5; cursor:not-allowed; }

    table.informes{ width:100%; border-collapse:collapse; font-size:14px; }
    table.informes th{
        text-align:left; font-family: var(--font-mono); font-size:11px;
        text-transform:uppercase; letter-spacing:.05em; color: var(--ink-soft);
        padding: 8px 10px; border-bottom: 1px solid var(--line);
    }
    table.informes td{ padding: 12px 10px; border-bottom: 1px solid var(--line); vertical-align:middle; }
    table.informes tr:last-child td{ border-bottom:none; }

    .sello{
        display:inline-block;
        font-family: var(--font-mono);
        font-size: 11px; font-weight:700; letter-spacing:.08em;
        text-transform:uppercase;
        padding: 4px 10px;
        border: 2px solid currentColor;
        border-radius: 3px;
        transform: rotate(-3deg);
    }
    .sello.pendiente{ color: var(--stamp-amber); }
    .sello.aprobado{ color: var(--stamp-green); }
    .sello.rechazado{ color: var(--stamp-red); }

    .empty-state{
        text-align:center; padding: 30px 10px; color: var(--ink-soft); font-size:13px;
    }

    .msg-tabs{ display:flex; gap:6px; }
    .msg-tab{
        font-family: var(--font-mono); font-size:12px;
        padding:7px 14px; border-radius: 4px 4px 0 0;
        background: var(--paper); color: var(--ink-soft); cursor:pointer;
        border: 1px solid var(--line); border-bottom:none;
    }
    .msg-tab.active{ background: var(--paper-raised); color: var(--ink); font-weight:600; }

    .chat-window{
        height: 320px; overflow-y:auto;
        display:flex; flex-direction:column; gap:10px;
        padding: 6px 4px 16px;
    }
    .chat-window[hidden]{ display:none; }
    .bubble{
        max-width: 65%;
        padding: 9px 13px;
        border-radius: 8px;
        font-size:14px;
        line-height:1.4;
    }
    .bubble .hora{ display:block; font-family: var(--font-mono); font-size:10px; margin-top:4px; opacity:.6; }
    .bubble.mio{ align-self:flex-end; background: var(--ink); color:#fff; border-bottom-right-radius:2px; }
    .bubble.otro{ align-self:flex-start; background: var(--paper); border: 1px solid var(--line); border-bottom-left-radius:2px; }

    .chat-form{ display:flex; gap:10px; border-top:1px solid var(--line); padding-top:14px; }
    .chat-form textarea{
        flex:1; resize:none; height:44px; padding:9px 10px;
        border:1px solid var(--line); border-radius:3px; font-family: var(--font-body); font-size:14px;
    }
    .chat-form button{
        font-family: var(--font-mono); font-size:12px; text-transform:uppercase;
        background: var(--folder-dark); color:#fff; border:none; border-radius:3px;
        padding: 0 18px; cursor:pointer;
    }

    .aviso-sin-practica{
        background: #FBF1E4; border:1px solid var(--folder); color: var(--folder-dark);
        padding: 12px 16px; border-radius:4px; font-size:13px; margin-bottom:20px;
    }

    @media (max-width: 820px){
        .practica-wrapper{ flex-direction:column; }
        .practica-sidebar{ width:100%; flex-direction:row; overflow-x:auto; padding:14px; }
        .sidebar-perfil{ display:none; }
        .sidebar-logout{ display:none; }
        .practica-main{ padding: 24px 18px; }
    }
</style>

<div class="practica-wrapper">
    <aside class="practica-sidebar">
        <div class="sidebar-perfil">
            <div class="sidebar-avatar"><?= htmlspecialchars(iniciales($estudiante["nombres"] ?? "?", $estudiante["apellidos"] ?? "")) ?></div>
            <h3><?= htmlspecialchars(($estudiante["nombres"] ?? "Estudiante") . " " . ($estudiante["apellidos"] ?? "")) ?></h3>
            <span><?= htmlspecialchars($estudiante["carrera"] ?? "Sin carrera asignada") ?></span>
        </div>

        <div class="sidebar-tab active" data-section="informes">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
            Mis Informes
        </div>
        <div class="sidebar-tab" data-section="mensajes">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            Mensajes
        </div>
        <div class="sidebar-tab" data-section="perfil">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-1a8 8 0 0 1 16 0v1"/></svg>
            Mi Perfil
        </div>

        <div class="sidebar-logout"><a href="../src/logout.php">Cerrar sesión →</a></div>
    </aside>

    <main class="practica-main">
        <div class="dashboard-main-header">
            <div class="eyebrow">Panel del estudiante</div>
            <h1><?= $id_practica ? "Tu práctica en " . htmlspecialchars($estudiante["empresa"]) : "Aún no tienes una práctica asignada" ?></h1>
            <?php if ($id_practica): ?>
                <div class="estado-practica"><span class="estado-dot"></span>
                    Estado: <?= htmlspecialchars($estudiante["estado_practica"]) ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!$id_practica): ?>
            <div class="aviso-sin-practica">
                Todavía no tienes una práctica registrada en el sistema. Cuando tu encargado de prácticas te asigne una,
                podrás subir informes y enviar mensajes a tu tutor y a tu encargado desde aquí.
            </div>
        <?php endif; ?>

        <!-- ================= SECCIÓN INFORMES ================= -->
        <section id="section-informes">
            <div class="panel">
                <div class="panel-head"><h2>Subir informe</h2></div>
                <div class="panel-body">
                    <form id="formInforme" action="../src/informes.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="subir_informe">
                        <input type="hidden" name="id_practica" value="<?= htmlspecialchars($id_practica ?? "") ?>">

                        <div class="dropzone" id="dropzone">
                            <strong>Arrastra tu archivo aquí</strong>
                            <p>o haz clic para seleccionarlo · PDF o Word, máx. 15MB</p>
                            <div class="filename" id="filename"></div>
                            <input type="file" name="archivo_informe" id="archivoInforme" accept=".pdf,.doc,.docx" required style="display:none;" <?= $id_practica ? "" : "disabled" ?>>
                        </div>

                        <div class="form-row">
                            <div class="field">
                                <label for="tipoInforme">Tipo de informe</label>
                                <select name="tipo_informe" id="tipoInforme" required <?= $id_practica ? "" : "disabled" ?>>
                                    <option value="" disabled selected>Selecciona un tipo</option>
                                    <option value="avance">Informe de avance</option>
                                    <option value="final">Informe final</option>
                                    <option value="autoevaluacion">Autoevaluación</option>
                                    <option value="bitacora">Bitácora semanal</option>
                                </select>
                            </div>
                            <div class="field">
                                <label for="comentario">Comentario (opcional)</label>
                                <input type="text" name="comentario" id="comentario" placeholder="Ej: corresponde al mes de julio" <?= $id_practica ? "" : "disabled" ?>>
                            </div>
                        </div>

                        <button type="submit" class="btn-stamp" <?= $id_practica ? "" : "disabled" ?>>Enviar informe</button>
                    </form>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head"><h2>Historial de informes</h2></div>
                <div class="panel-body">
                    <?php if (empty($informes)): ?>
                        <div class="empty-state">Todavía no has subido informes. El primero que envíes aparecerá aquí.</div>
                    <?php else: ?>
                        <table class="informes">
                            <thead>
                                <tr><th>Archivo</th><th>Tipo</th><th>Fecha</th><th>Estado</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach ($informes as $inf): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($inf["nombre_archivo"]) ?></td>
                                        <td><?= htmlspecialchars(ucfirst($inf["tipo_informe"])) ?></td>
                                        <td><?= htmlspecialchars(date("d-m-Y", strtotime($inf["fecha_subida"]))) ?></td>
                                        <td><span class="sello <?= htmlspecialchars($inf["estado"]) ?>"><?= htmlspecialchars($inf["estado"]) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- ================= SECCIÓN MENSAJES ================= -->
        <section id="section-mensajes" hidden>
            <div class="panel">
                <div class="panel-head" style="border-bottom:none; padding-bottom:0;">
                    <div class="msg-tabs">
                        <div class="msg-tab active" data-thread="tutor">
                            Tutor Laboral <?= !empty($estudiante["tutor_nombres"]) ? "· " . htmlspecialchars($estudiante["tutor_nombres"]) : "" ?>
                        </div>
                        <div class="msg-tab" data-thread="encargado">
                            Encargado de Prácticas <?= !empty($estudiante["encargado_nombres"]) ? "· " . htmlspecialchars($estudiante["encargado_nombres"]) : "" ?>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="chat-window" id="chat-tutor">
                        <?php if (empty($mensajes_tutor)): ?>
                            <div class="empty-state">Aún no tienes mensajes con tu tutor laboral.</div>
                        <?php else: foreach ($mensajes_tutor as $m):
                            $mio = $m["emisor_correo"] === $correo_sesion; ?>
                            <div class="bubble <?= $mio ? "mio" : "otro" ?>">
                                <?= nl2br(htmlspecialchars($m["contenido"])) ?>
                                <span class="hora"><?= htmlspecialchars(date("d-m H:i", strtotime($m["fecha_envio"]))) ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>
                    <div class="chat-window" id="chat-encargado" hidden>
                        <?php if (empty($mensajes_encargado)): ?>
                            <div class="empty-state">Aún no tienes mensajes con tu encargado de prácticas.</div>
                        <?php else: foreach ($mensajes_encargado as $m):
                            $mio = $m["emisor_correo"] === $correo_sesion; ?>
                            <div class="bubble <?= $mio ? "mio" : "otro" ?>">
                                <?= nl2br(htmlspecialchars($m["contenido"])) ?>
                                <span class="hora"><?= htmlspecialchars(date("d-m H:i", strtotime($m["fecha_envio"]))) ?></span>
                            </div>
                        <?php endforeach; endif; ?>
                    </div>

                    <form class="chat-form" id="formMensaje" action="../src/mensajes.php" method="POST">
                        <input type="hidden" name="action" value="enviar_mensaje">
                        <input type="hidden" name="id_practica" value="<?= htmlspecialchars($id_practica ?? "") ?>">
                        <input type="hidden" name="destinatario_rol" id="destinatarioRol" value="tutor">
                        <textarea name="contenido" placeholder="Escribe tu mensaje..." required <?= $id_practica ? "" : "disabled" ?>></textarea>
                        <button type="submit" <?= $id_practica ? "" : "disabled" ?>>Enviar</button>
                    </form>
                </div>
            </div>
        </section>

        <!-- ================= SECCIÓN PERFIL ================= -->
        <section id="section-perfil" hidden>
            <div class="panel">
                <div class="panel-head"><h2>Mi Perfil</h2></div>
                <div class="panel-body">
                    <div class="form-row">
                        <div class="field"><label>Nombre completo</label><input type="text" value="<?= htmlspecialchars(($estudiante["nombres"] ?? "") . " " . ($estudiante["apellidos"] ?? "")) ?>" disabled></div>
                        <div class="field"><label>RUT</label><input type="text" value="<?= htmlspecialchars($estudiante["rut"] ?? "") ?>" disabled></div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Correo</label><input type="text" value="<?= htmlspecialchars($estudiante["correo"] ?? "") ?>" disabled></div>
                        <div class="field"><label>Universidad</label><input type="text" value="<?= htmlspecialchars($estudiante["universidad"] ?? "") ?>" disabled></div>
                    </div>
                    <div class="form-row">
                        <div class="field"><label>Carrera</label><input type="text" value="<?= htmlspecialchars($estudiante["carrera"] ?? "") ?>" disabled></div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.sidebar-tab');
    const sections = {
        informes: document.getElementById('section-informes'),
        mensajes: document.getElementById('section-mensajes'),
        perfil: document.getElementById('section-perfil'),
    };
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            Object.values(sections).forEach(s => s.hidden = true);
            sections[tab.dataset.section].hidden = false;
        });
    });

    const dropzone = document.getElementById('dropzone');
    const fileInput = document.getElementById('archivoInforme');
    const filenameLabel = document.getElementById('filename');

    dropzone.addEventListener('click', () => { if (!fileInput.disabled) fileInput.click(); });
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.classList.add('dragover'); });
    dropzone.addEventListener('dragleave', () => dropzone.classList.remove('dragover'));
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        dropzone.classList.remove('dragover');
        if (fileInput.disabled) return;
        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            mostrarNombreArchivo();
        }
    });
    fileInput.addEventListener('change', mostrarNombreArchivo);

    function mostrarNombreArchivo() {
        if (fileInput.files.length) {
            filenameLabel.textContent = "📎 " + fileInput.files[0].name;
            filenameLabel.style.display = 'block';
        }
    }

    const msgTabs = document.querySelectorAll('.msg-tab');
    const chatTutor = document.getElementById('chat-tutor');
    const chatEncargado = document.getElementById('chat-encargado');
    const destinatarioRol = document.getElementById('destinatarioRol');

    msgTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            msgTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const esTutor = tab.dataset.thread === 'tutor';
            chatTutor.hidden = !esTutor;
            chatEncargado.hidden = esTutor;
            destinatarioRol.value = esTutor ? 'tutor' : 'encargado';
        });
    });

    [chatTutor, chatEncargado].forEach(c => c.scrollTop = c.scrollHeight);
});
</script>

<?php include "../templates/footer.php"; ?>
