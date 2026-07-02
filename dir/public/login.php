<?php
require_once "../config/db.php";

$sql_carreras = "SELECT c.id_career, c.nombre as carrera, u.nombre as universidad
                 FROM carreras c
                 JOIN sedes s ON c.id_sede = s.id_sede
                 JOIN universidades u ON s.id_university = u.id_universidad
                 ORDER BY u.nombre, c.nombre";
$carreras_bd = $pdo->query($sql_carreras)->fetchAll(PDO::FETCH_ASSOC);

$carreras_agrupadas = [];
foreach ($carreras_bd as $row) {
    $carreras_agrupadas[$row["universidad"]][] = $row;
}

$empresas = $pdo
    ->query("SELECT id_empresa, nombre FROM empresa ORDER BY nombre")
    ->fetchAll(PDO::FETCH_ASSOC);

include "../templates/header.php";
?>

<div class="login-wrapper">
    <div class="login-box">
        <div class="side-form">
            <div class="form-tabs">
                <button class="tab-btn active" data-target="login">Iniciar sesión</button>
                <button class="tab-btn" data-target="register">Registro</button>
            </div>

            <div class="forms-container">
                <div class="form-slider" id="formSlider">
                    <form id="loginForm" action="../src/login.php" method="POST" class="form-pane active">
                        <input type="hidden" name="action" value="login">
                        <h2>¡Bienvenido!</h2>
                        <p class="subtitle">Ingresa tus datos para continuar</p>

                        <?php if (isset($_GET["error"])): ?>
                            <div style="background-color: #ffcccc; color: #cc0000; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold;">
                                La cuenta no existe o los datos son incorrectos.
                            </div>
                        <?php endif; ?>

                        <div class="field"><input type="email" name="email" placeholder="Correo electrónico" required></div>
                        <div class="field"><input type="password" name="password" placeholder="Contraseña" required></div>
                        <div class="options">
                            <label><input type="checkbox"> Recordarme</label>
                            <a href="recuperacion.php">Olvide mi contraseña</a>
                        </div>
                        <button type="submit" class="btn-primary">Entrar</button>
                    </form>

                    <form id="registerForm" action="../src/login.php" method="POST" class="form-pane">
                        <input type="hidden" name="action" value="register">
                        <h2>Crea tu cuenta</h2>
                        <p class="subtitle">Registrate a nombre de tu institucion</p>

                        <div class="field">
                            <select name="rol_registro" id="rolSelect" required class="custom-select">
                                <option value="" disabled selected>Elige tu cargo/rol</option>
                                <option value="director">Director</option>
                                <option value="encargado">Encargado de Prácticas</option>
                                <option value="estudiante">Alumno</option>
                                <option value="tutor">Tutor Laboral</option>
                            </select>
                        </div>

                        <div class="field"><input type="text" name="nombres" placeholder="Nombres" required></div>
                        <div class="field"><input type="text" name="apellidos" placeholder="Apellidos" required></div>
                        <div class="field"><input type="email" name="email" placeholder="Correo electrónico" required></div>
                        <div class="field"><input type="password" name="password" placeholder="Contraseña" required></div>

                        <div id="dynamicFields"></div>

                        <button type="submit" class="btn-primary" style="margin-top: 15px;">Registrarse</button>
                    </form>
                </div>
            </div>

            <div class="scroll-track">
                <div class="scroll-indicator" id="scrollIndicator"></div>
            </div>
        </div>

        <div class="side-info">
            <div class="info-text">
                <h1>ES HORA DE TRABAJAR</h1>
                <p>Inicia sesion o registrate para acceder a las funciones de seguimiento que tenemos disposnibles para ti.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rolSelect');
    const dynamicFields = document.getElementById('dynamicFields');


    const htmlCarreras = `
        <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.3s;">
            <select name="id_carrera" class="custom-select" required>
                <option value="" disabled selected>Selecciona Universidad y Carrera</option>
                <?php foreach (
                    $carreras_agrupadas
                    as $uni => $lista_carreras
                ): ?>
                    <optgroup label="<?= htmlspecialchars($uni) ?>">
                        <?php foreach ($lista_carreras as $c): ?>
                            <option value="<?= $c[
                                "id_career"
                            ] ?>"><?= htmlspecialchars(
    $c["carrera"],
) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
    `;

    const htmlEmpresas = `
        <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.3s;">
            <select name="id_empresa" class="custom-select" required>
                <option value="" disabled selected>Selecciona tu Empresa</option>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e[
                        "id_empresa"
                    ] ?>"><?= htmlspecialchars($e["nombre"]) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    `;

    rolSelect.addEventListener('change', function() {
        let fieldsHTML = '';

        switch(this.value) {
            case 'estudiante':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="number" name="rut" placeholder="RUT (Sin puntos ni guion)" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.2s;"><input type="text" name="cod_invitacion" placeholder="Código de invitación del Encargado" required></div>`
                              + htmlCarreras;
                break;
            case 'encargado':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="text" name="telefono" placeholder="Teléfono de contacto" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.2s;"><input type="text" name="cod_invitacion" placeholder="Código de invitación del Director" required></div>`
                              + htmlCarreras;
                break;
            case 'director':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="text" name="telefono" placeholder="Teléfono de contacto" required></div>`
                              + htmlCarreras;
                break;
            case 'tutor':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="text" name="cargo" placeholder="Tu Cargo en la Empresa" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.2s;"><input type="text" name="telefono" placeholder="Teléfono" required></div>`
                              + htmlEmpresas;
                break;
        }

        dynamicFields.innerHTML = fieldsHTML;


        if (typeof updateScrollIndicator === 'function') {
            setTimeout(updateScrollIndicator, 50);
        }
    });
});
</script>

<?php
include "../templates/footer.php";
require_once "../config/db.php";

$sql_carreras = "SELECT c.id_career, c.nombre as carrera, u.nombre as universidad
                 FROM carreras c
                 JOIN sedes s ON c.id_sede = s.id_sede
                 JOIN universidades u ON s.id_university = u.id_universidad
                 ORDER BY u.nombre, c.nombre";
$carreras_bd = $pdo->query($sql_carreras)->fetchAll(PDO::FETCH_ASSOC);

$carreras_agrupadas = [];
foreach ($carreras_bd as $row) {
    $carreras_agrupadas[$row["universidad"]][] = $row;
}

$empresas = $pdo
    ->query("SELECT id_empresa, nombre FROM empresa ORDER BY nombre")
    ->fetchAll(PDO::FETCH_ASSOC);

include "../templates/header.php";
?>

<div class="login-wrapper">
    <div class="login-box">
        <div class="side-form">
            <div class="form-tabs">
                <button class="tab-btn active" data-target="login">Iniciar sesión</button>
                <button class="tab-btn" data-target="register">Registro</button>
            </div>

            <div class="forms-container">
                <div class="form-slider" id="formSlider">
                    <form id="loginForm" action="../src/login.php" method="POST" class="form-pane active">
                        <input type="hidden" name="action" value="login">
                        <h2>¡Bienvenido!</h2>
                        <p class="subtitle">Ingresa tus datos para continuar</p>

                        <?php if (isset($_GET["error"])): ?>
                            <div style="background-color: #ffcccc; color: #cc0000; padding: 10px; border-radius: 5px; margin-bottom: 15px; text-align: center; font-weight: bold;">
                                La cuenta no existe o los datos son incorrectos.
                            </div>
                        <?php endif; ?>

                        <div class="field"><input type="email" name="email" placeholder="Correo electrónico" required></div>
                        <div class="field"><input type="password" name="password" placeholder="Contraseña" required></div>
                        <div class="options">
                            <label><input type="checkbox"> Recordarme</label>
                            <a href="recuperacion.php">Olvide mi contraseña</a>
                        </div>
                        <button type="submit" class="btn-primary">Entrar</button>
                    </form>

                    <form id="registerForm" action="../src/login.php" method="POST" class="form-pane">
                        <input type="hidden" name="action" value="register">
                        <h2>Crea tu cuenta</h2>
                        <p class="subtitle">Registrate a nombre de tu institucion</p>

                        <div class="field">
                            <select name="rol_registro" id="rolSelect" required class="custom-select">
                                <option value="" disabled selected>Elige tu cargo/rol</option>
                                <option value="director">Director</option>
                                <option value="encargado">Encargado de Prácticas</option>
                                <option value="estudiante">Alumno</option>
                                <option value="tutor">Tutor Laboral</option>
                            </select>
                        </div>

                        <div class="field"><input type="text" name="nombres" placeholder="Nombres" required></div>
                        <div class="field"><input type="text" name="apellidos" placeholder="Apellidos" required></div>
                        <div class="field"><input type="email" name="email" placeholder="Correo electrónico" required></div>
                        <div class="field"><input type="password" name="password" placeholder="Contraseña" required></div>

                        <div id="dynamicFields"></div>

                        <button type="submit" class="btn-primary" style="margin-top: 15px;">Registrarse</button>
                    </form>
                </div>
            </div>

            <div class="scroll-track">
                <div class="scroll-indicator" id="scrollIndicator"></div>
            </div>
        </div>

        <div class="side-info">
            <div class="info-text">
                <h1>ES HORA DE TRABAJAR</h1>
                <p>Inicia sesion o registrate para acceder a las funciones de seguimiento que tenemos disposnibles para ti.</p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rolSelect = document.getElementById('rolSelect');
    const dynamicFields = document.getElementById('dynamicFields');


    const htmlCarreras = `
        <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.3s;">
            <select name="id_carrera" class="custom-select" required>
                <option value="" disabled selected>Selecciona Universidad y Carrera</option>
                <?php foreach (
                    $carreras_agrupadas
                    as $uni => $lista_carreras
                ): ?>
                    <optgroup label="<?= htmlspecialchars($uni) ?>">
                        <?php foreach ($lista_carreras as $c): ?>
                            <option value="<?= $c[
                                "id_career"
                            ] ?>"><?= htmlspecialchars(
    $c["carrera"],
) ?></option>
                        <?php endforeach; ?>
                    </optgroup>
                <?php endforeach; ?>
            </select>
        </div>
    `;


    const htmlEmpresas = `
        <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.3s;">
            <select name="id_empresa" class="custom-select" required>
                <option value="" disabled selected>Selecciona tu Empresa</option>
                <?php foreach ($empresas as $e): ?>
                    <option value="<?= $e[
                        "id_empresa"
                    ] ?>"><?= htmlspecialchars($e["nombre"]) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    `;

    rolSelect.addEventListener('change', function() {
        let fieldsHTML = '';

        switch(this.value) {
            case 'estudiante':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="number" name="rut" placeholder="RUT (Sin puntos ni guion)" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.2s;"><input type="text" name="cod_invitacion" placeholder="Código de invitación del Encargado" required></div>`
                              + htmlCarreras;
                break;
            case 'encargado':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="text" name="telefono" placeholder="Teléfono de contacto" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.2s;"><input type="text" name="cod_invitacion" placeholder="Código de invitación del Director" required></div>`
                              + htmlCarreras;
                break;
            case 'director':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="text" name="universidad" placeholder="Nombre de la Universidad" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.2s;"><input type="text" name="carrera" placeholder="Nombre de la Carrera" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.3s;"><input type="text" name="telefono" placeholder="Teléfono de contacto" required></div>`;
                break;
            case 'tutor':
                fieldsHTML = `<div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.1s;"><input type="text" name="cargo" placeholder="Tu Cargo en la Empresa" required></div>
                              <div class="field dynamic-field" style="margin-top: 10px; animation-delay: 0.2s;"><input type="text" name="telefono" placeholder="Teléfono" required></div>`
                              + htmlEmpresas;
                break;
        }

        dynamicFields.innerHTML = fieldsHTML;

        if (typeof updateScrollIndicator === 'function') {
            setTimeout(updateScrollIndicator, 50);
        }
    });
});
</script>

<?php include "../templates/footer.php"; ?>
