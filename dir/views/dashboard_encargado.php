<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
require_once __DIR__ . "/../config/db.php";

$flashMessage = null;
$flashType = 'success';
if (!empty($_SESSION['flash'])) {
    $flashMessage = $_SESSION['flash']['message'] ?? null;
    $flashType = $_SESSION['flash']['type'] ?? 'success';
    unset($_SESSION['flash']);
}

if (!isset($_SESSION["correo"]) || $_SESSION["rol"] !== "encargados") {
    header("Location: ../public/login.php?error=1");
    exit();
}

$correo_sesion = $_SESSION["correo"];

$stmt = $pdo->prepare(
    "SELECT id_encargado, nombres, apellidos, correo, telefono, id_carrera
     FROM encargados
     WHERE correo = :correo"
);
$stmt->execute(["correo" => $correo_sesion]);
$encargado = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

$encargado_id = $encargado["id_encargado"] ?? null;

$estudiantes = [];
if ($encargado_id) {
    $sql = "SELECT
                e.id_estudiante, e.nombres, e.apellidos, e.correo, e.rut,
                c.nombre AS carrera,
                p.id_practica, p.fecha_inicio_practica, p.fecha_termino_practica,
                est.nombre AS estado_practica,
                emp.nombre AS empresa,
                t.nombres AS tutor_nombres, t.apellidos AS tutor_apellidos
            FROM practicas p
            JOIN estudiantes e ON e.id_estudiante = p.id_estudiante
            JOIN carreras c ON c.id_career = e.id_carrera
            LEFT JOIN estados est ON est.id_estado = p.id_estado
            LEFT JOIN tutores t ON t.id_tutor = p.id_tutor
            LEFT JOIN empresa emp ON emp.id_empresa = t.id_empresa
            WHERE p.id_encargado = :id_encargado
            ORDER BY p.id_practica DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["id_encargado" => $encargado_id]);
    $practicas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $columnStmt = $pdo->query("SHOW COLUMNS FROM documentos");
    $documentColumns = [];
    foreach ($columnStmt as $column) {
        $documentColumns[$column["Field"]] = true;
    }

    foreach ($practicas as $practica) {
        $idPractica = $practica["id_practica"] ?? null;
        $documentos = [];
        if ($idPractica && isset($documentColumns["id_practica"])) {
            $selectParts = ["id_documentos"];
            $selectParts[] = isset($documentColumns["titulo"]) ? "titulo" : "NULL AS titulo";
            $selectParts[] = isset($documentColumns["tipo"]) ? "tipo" : "NULL AS tipo";
            $selectParts[] = isset($documentColumns["tipo_informe"]) ? "tipo_informe" : "NULL AS tipo_informe";
            $selectParts[] = isset($documentColumns["nombre_archivo"]) ? "nombre_archivo" : "NULL AS nombre_archivo";
            $selectParts[] = isset($documentColumns["estado"]) ? "estado" : "'pendiente' AS estado";
            $selectParts[] = isset($documentColumns["comentario"]) ? "comentario" : "NULL AS comentario";
            $selectParts[] = isset($documentColumns["fecha_subida"]) ? "fecha_subida" : "NULL AS fecha_subida";

            $docSql = "SELECT " . implode(", ", $selectParts) . "
                       FROM documentos
                       WHERE id_practica = :id_practica
                       ORDER BY id_documentos DESC";

            $docStmt = $pdo->prepare($docSql);
            $docStmt->execute(["id_practica" => $idPractica]);
            $documentos = $docStmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $estudiantes[] = [
            "estudiante" => $practica,
            "documentos" => $documentos,
        ];
    }
}

function iniciales($nombres, $apellidos) {
    $n = substr(trim((string) $nombres), 0, 1);
    $a = substr(trim((string) $apellidos), 0, 1);
    return strtoupper($n . $a);
}

include __DIR__ . "/../templates/header.php";
?>

<style>
    :root {
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
        --font-display:'Source Serif 4', Georgia, serif;
        --font-mono:'IBM Plex Mono', 'Courier New', monospace;
        --font-body:'Source Sans 3', Arial, sans-serif;
    }

    body > .main-header,
    body > #bg-video,
    body > .bg-decoration { display:none !important; }
    body { background:var(--paper) !important; margin:0; }
    body > main#app-content { padding:0; display:block; }

    .practica-wrapper { display:flex; min-height:100vh; background:var(--paper); color:var(--ink); font-family:var(--font-body); }
    .practica-sidebar { width:250px; flex-shrink:0; background:var(--ink); color:var(--paper-raised); padding:28px 0; display:flex; flex-direction:column; gap:4px; }
    .sidebar-perfil { padding:0 22px 22px; border-bottom:1px solid rgba(255,255,255,.12); margin-bottom:18px; }
    .sidebar-avatar { width:48px;height:48px;border-radius:6px; background:var(--folder); color:var(--ink); display:flex; align-items:center; justify-content:center; font-weight:700; margin-bottom:10px; font-family:var(--font-mono); }
    .sidebar-perfil h3 { font-family:var(--font-display); font-size:17px; margin:0 0 2px; }
    .sidebar-perfil span { font-size:12px; color:#B9C2CA; font-family:var(--font-mono); }
    .sidebar-tab { padding:12px 22px 12px 28px; font-family:var(--font-mono); font-size:13px; letter-spacing:.03em; color:#C7CED4; text-decoration:none; border-left:3px solid transparent; cursor:pointer; display:flex; align-items:center; gap:10px; }
    .sidebar-tab.active { color:#fff; border-left-color:var(--folder); background:rgba(255,255,255,.06); }
    .sidebar-logout { margin-top:auto; padding:12px 22px 0 28px; font-size:12px; font-family:var(--font-mono); }
    .sidebar-logout a { color:#E4B2AC; text-decoration:none; }

    .practica-main { flex:1; padding:34px 44px; max-width:1200px; }
    .dashboard-main-header { margin-bottom:26px; display:flex; flex-direction:column; gap:8px; }
    .eyebrow { font-family:var(--font-mono); font-size:11px; letter-spacing:.12em; text-transform:uppercase; color:var(--folder-dark); margin-bottom:2px; }
    .dashboard-main-header h1 { font-family:var(--font-display); font-size:30px; margin:0; }
    .panel { background:var(--paper-raised); border:1px solid var(--line); border-radius:4px; margin-bottom:26px; }
    .panel-head { padding:16px 22px; border-bottom:1px solid var(--line); display:flex; align-items:center; justify-content:space-between; }
    .panel-head h2 { font-family:var(--font-display); font-size:19px; margin:0; }
    .panel-body { padding:22px; }
    .student-card { border:1px solid var(--line); border-radius:4px; padding:16px; margin-bottom:14px; background:#fff; }
    .student-card-head { display:flex; justify-content:space-between; align-items:center; gap:10px; margin-bottom:10px; }
    .student-card h3 { margin:0; font-size:16px; font-family:var(--font-display); }
    .badge { display:inline-block; font-family:var(--font-mono); font-size:11px; text-transform:uppercase; letter-spacing:.05em; padding:4px 8px; border-radius:999px; background:var(--paper); color:var(--ink-soft); }
    .badge.aprobado { background:rgba(63,108,78,.14); color:var(--stamp-green); }
    .badge.pendiente { background:rgba(166,117,44,.16); color:var(--stamp-amber); }
    .badge.rechazado { background:rgba(156,59,52,.15); color:var(--stamp-red); }
    .doc-item { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 0; border-bottom:1px solid var(--line); }
    .doc-item:last-child { border-bottom:none; }
    .doc-item strong { display:block; font-size:14px; }
    .doc-item span { font-size:12px; color:var(--ink-soft); }
    .doc-actions { display:flex; gap:8px; }
    .btn-chip { border:none; padding:8px 10px; border-radius:3px; cursor:pointer; font-family:var(--font-mono); font-size:11px; text-transform:uppercase; }
    .btn-chip.approve { background:var(--stamp-green); color:#fff; }
    .btn-chip.reject { background:var(--stamp-red); color:#fff; }
    .empty-state { text-align:center; padding:30px 10px; color:var(--ink-soft); font-size:13px; }
    .msg-tabs { display:flex; gap:6px; flex-wrap:wrap; }
    .msg-tab { font-family:var(--font-mono); font-size:12px; padding:7px 14px; border-radius:4px 4px 0 0; background:var(--paper); border:1px solid var(--line); border-bottom:none; cursor:pointer; }
    .msg-tab.active { background:var(--paper-raised); color:var(--ink); font-weight:600; }
    .chat-window { height:280px; overflow-y:auto; padding:8px 4px 12px; display:flex; flex-direction:column; gap:10px; }
    .chat-window[hidden] { display:none; }
    .bubble { max-width:70%; padding:9px 12px; border-radius:8px; font-size:14px; line-height:1.4; }
    .bubble.otro { align-self:flex-start; background:var(--paper); border:1px solid var(--line); }
    .bubble.mio { align-self:flex-end; background:var(--ink); color:#fff; }
    .bubble .hora { display:block; font-size:10px; margin-top:4px; opacity:.6; }
    .chat-form { display:flex; gap:10px; border-top:1px solid var(--line); padding-top:14px; }
    .chat-form textarea { flex:1; resize:none; height:44px; padding:9px 10px; border:1px solid var(--line); border-radius:3px; }
    .chat-form button { background:var(--folder-dark); color:#fff; border:none; padding:0 16px; border-radius:3px; cursor:pointer; font-family:var(--font-mono); font-size:12px; text-transform:uppercase; }
    @media (max-width:820px){ .practica-wrapper{flex-direction:column;} .practica-sidebar{width:100%; flex-direction:row; overflow-x:auto; padding:14px;} .sidebar-perfil{display:none;} .sidebar-logout{display:none;} .practica-main{padding:24px 18px;} }
</style>

<div class="practica-wrapper">
    <aside class="practica-sidebar">
        <div class="sidebar-perfil">
            <div class="sidebar-avatar"><?= htmlspecialchars(iniciales($encargado["nombres"] ?? "?", $encargado["apellidos"] ?? "")) ?></div>
            <h3><?= htmlspecialchars(($encargado["nombres"] ?? "Encargado") . " " . ($encargado["apellidos"] ?? "")) ?></h3>
            <span>Encargado de Prácticas</span>
        </div>

        <div class="sidebar-tab active" data-section="revision">Revisión documental</div>
        <div class="sidebar-tab" data-section="practicas">Aprobación de prácticas</div>
        <div class="sidebar-tab" data-section="mensajes">Mensajes</div>

        <div class="sidebar-logout"><a href="../src/logout.php">Cerrar sesión →</a></div>
    </aside>

    <main class="practica-main">
        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= htmlspecialchars((string) $flashType) ?>" style="margin-bottom:16px; padding:12px 14px; border-radius:4px; background:<?= $flashType === 'error' ? '#fce8e8' : '#e9f7ee' ?>; color:<?= $flashType === 'error' ? '#8a1f1f' : '#1f5a3a' ?>; border:1px solid <?= $flashType === 'error' ? '#e5b1b1' : '#b8d9c4' ?>;">
                <?= htmlspecialchars((string) $flashMessage) ?>
            </div>
        <?php endif; ?>

        <div class="dashboard-main-header">
            <div class="eyebrow">Panel del encargado</div>
            <h1>Gestiona documentos, aprobaciones y comunicación con los estudiantes</h1>
        </div>

        <section id="section-revision">
            <div class="panel">
                <div class="panel-head"><h2>Documentos pendientes de validación</h2></div>
                <div class="panel-body">
                    <?php if (empty($estudiantes)): ?>
                        <div class="empty-state">No hay estudiantes asociados a tu cargo por el momento.</div>
                    <?php else: ?>
                        <?php foreach ($estudiantes as $entry):
                            $student = $entry["estudiante"];
                            $docs = $entry["documentos"];
                        ?>
                            <div class="student-card">
                                <div class="student-card-head">
                                    <div>
                                        <h3><?= htmlspecialchars(($student["nombres"] ?? "") . " " . ($student["apellidos"] ?? "")) ?></h3>
                                        <div style="font-size:12px;color:var(--ink-soft);margin-top:4px;">
                                            <?= htmlspecialchars($student["carrera"] ?? "Sin carrera") ?> · <?= htmlspecialchars($student["correo"] ?? "") ?>
                                        </div>
                                    </div>
                                    <span class="badge <?= strtolower((string) ($student["estado_practica"] ?? "pendiente")) ?>">
                                        <?= htmlspecialchars($student["estado_practica"] ?? "Pendiente") ?>
                                    </span>
                                </div>

                                <?php if (empty($docs)): ?>
                                    <div class="empty-state">El estudiante aún no ha subido documentos para esta práctica.</div>
                                <?php else: ?>
                                    <?php foreach ($docs as $doc): ?>
                                        <div class="doc-item">
                                            <div>
                                                <strong><?= htmlspecialchars($doc["titulo"] ?? $doc["nombre_archivo"] ?? "Documento") ?></strong>
                                                <span><?= htmlspecialchars($doc["tipo"] ?? $doc["tipo_informe"] ?? "Sin tipo") ?> · <?= htmlspecialchars($doc["estado"] ?? "pendiente") ?></span>
                                            </div>
                                        <div style="margin-top:10px;">
                                            <form action="../src/informes.php" method="POST" style="display:flex; flex-direction:column; gap:8px;">
                                                <input type="hidden" name="action" value="revisar_documento">
                                                <input type="hidden" name="id_documentos" value="<?= htmlspecialchars((string) ($doc['id_documentos'] ?? '')) ?>">
                                                <textarea name="comentario_revisor" rows="2" placeholder="Agrega una observación para el estudiante" style="width:100%; border:1px solid var(--line); border-radius:3px; padding:8px;"></textarea>
                                                <div class="doc-actions">
                                                    <button class="btn-chip approve" type="submit" name="estado" value="aprobado">Aprobar</button>
                                                    <button class="btn-chip reject" type="submit" name="estado" value="rechazado">Rechazar</button>
                                                </div>
                                            </form>
                                        </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="section-practicas" hidden>
            <div class="panel">
                <div class="panel-head"><h2>Aprobación de práctica</h2></div>
                <div class="panel-body">
                    <?php if (empty($estudiantes)): ?>
                        <div class="empty-state">No hay prácticas para revisar.</div>
                    <?php else: ?>
                        <?php foreach ($estudiantes as $entry):
                            $student = $entry["estudiante"];
                        ?>
                            <div class="student-card">
                                <div class="student-card-head">
                                    <div>
                                        <h3><?= htmlspecialchars(($student["nombres"] ?? "") . " " . ($student["apellidos"] ?? "")) ?></h3>
                                        <div style="font-size:12px;color:var(--ink-soft);margin-top:4px;">
                                            Inicio: <?= htmlspecialchars($student["fecha_inicio_practica"] ?? "Sin fecha") ?> · Término: <?= htmlspecialchars($student["fecha_termino_practica"] ?? "Sin fecha") ?>
                                        </div>
                                    </div>
                                    <span class="badge <?= strtolower((string) ($student["estado_practica"] ?? "pendiente")) ?>">
                                        <?= htmlspecialchars($student["estado_practica"] ?? "Pendiente") ?>
                                    </span>
                                </div>
                                <div class="doc-actions" style="margin-top:8px;">
                                    <button class="btn-chip approve" type="button">Aprobar práctica</button>
                                    <button class="btn-chip reject" type="button">Solicitar ajustes</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section id="section-mensajes" hidden>
            <div class="panel">
                <div class="panel-head" style="border-bottom:none; padding-bottom:0;">
                    <div class="msg-tabs">
                        <?php foreach ($estudiantes as $index => $entry):
                            $student = $entry["estudiante"];
                            $label = ($student["nombres"] ?? "Estudiante") . " " . ($student["apellidos"] ?? "");
                            $practicaId = $student["id_practica"] ?? "";
                        ?>
                            <div class="msg-tab <?= $index === 0 ? "active" : "" ?>" data-practica-id="<?= htmlspecialchars((string) $practicaId) ?>">
                                <?= htmlspecialchars($label) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="panel-body">
                    <?php if (empty($estudiantes)): ?>
                        <div class="empty-state">No hay estudiantes con quienes conversar aún.</div>
                    <?php else: ?>
                        <div class="chat-window" id="chatWindow"></div>
                        <form class="chat-form" id="chatForm" data-current-email="<?= htmlspecialchars($correo_sesion) ?>">
                            <input type="hidden" name="action" value="enviar_mensaje">
                            <input type="hidden" name="id_practica" id="chatPracticaId" value="">
                            <input type="hidden" name="destinatario_rol" id="chatDestinatarioRol" value="encargado">
                            <textarea id="chatInput" name="contenido" placeholder="Escribe un mensaje para el estudiante..."></textarea>
                            <button type="submit">Enviar</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.sidebar-tab');
    const sections = {
        revision: document.getElementById('section-revision'),
        practicas: document.getElementById('section-practicas'),
        mensajes: document.getElementById('section-mensajes'),
    };

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            Object.values(sections).forEach(section => section.hidden = true);
            sections[tab.dataset.section].hidden = false;
        });
    });

    const msgTabs = document.querySelectorAll('.msg-tab');
    const chatWindow = document.getElementById('chatWindow');
    const chatForm = document.getElementById('chatForm');
    const chatInput = document.getElementById('chatInput');
    const chatPracticaId = document.getElementById('chatPracticaId');
    const chatDestinatarioRol = document.getElementById('chatDestinatarioRol');
    const currentUserEmail = chatForm ? chatForm.dataset.currentEmail || '' : '';

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\"/g, '&quot;');
    }

    function renderMessages(messages) {
        chatWindow.innerHTML = '';
        if (!messages.length) {
            chatWindow.innerHTML = '<div class="empty-state">No hay mensajes en esta conversación todavía.</div>';
            return;
        }

        messages.forEach(message => {
            const bubble = document.createElement('div');
            const esMio = String(message.emisor_correo || '') === currentUserEmail;
            bubble.className = 'bubble ' + (esMio ? 'mio' : 'otro');
            bubble.innerHTML = `${escapeHtml(message.contenido || '')}<span class="hora">${escapeHtml(message.fecha_envio ? message.fecha_envio.replace(' ', ' · ').slice(0, 16) : 'Ahora')}</span>`;
            chatWindow.appendChild(bubble);
        });

        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    function cargarMensajes(practicaId) {
        if (!practicaId) {
            chatWindow.innerHTML = '<div class="empty-state">Selecciona un estudiante para ver su conversación.</div>';
            return;
        }

        fetch('../src/mensajes.php?id_practica=' + encodeURIComponent(practicaId) + '&destinatario_rol=' + encodeURIComponent(chatDestinatarioRol.value), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    renderMessages(data.mensajes || []);
                }
            })
            .catch(() => {
                chatWindow.innerHTML = '<div class="empty-state">No se pudieron cargar los mensajes en este momento.</div>';
            });
    }

    msgTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            msgTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            const practicaId = tab.dataset.practicaId || '';
            chatPracticaId.value = practicaId;
            cargarMensajes(practicaId);
        });
    });

    if (chatForm) {
        chatForm.addEventListener('submit', function (e) {
            e.preventDefault();
            if (!chatInput.value.trim() || !chatPracticaId.value) return;

            const formData = new FormData(chatForm);
            fetch('../src/mensajes.php', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        chatInput.value = '';
                        cargarMensajes(chatPracticaId.value);
                    }
                });
        });
    }

    const firstTab = document.querySelector('.msg-tab.active');
    if (firstTab) {
        chatPracticaId.value = firstTab.dataset.practicaId || '';
        cargarMensajes(chatPracticaId.value);
    }
});
</script>

<?php include __DIR__ . "/../templates/footer.php"; ?>
