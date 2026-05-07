<?php include '../templates/header.php'; ?>

<div class="login-wrapper">
    <div class="login-box">
        <div class="side-form">
            <div class="form-tabs">
                <button class="tab-btn active" data-target="login">Iniciar sesión</button>
                <button class="tab-btn" data-target="register">Registro</button>
            </div>
            
            <div class="forms-container">
                <div class="form-slider" id="formSlider">
<<<<<<< HEAD
                    <form id="loginForm" action="../src/login.php" method="POST" class="form-pane active">
=======
                    <form id="loginForm" action="../backend/login.php" method="POST" class="form-pane active">
>>>>>>> 8ad76e6dd18c8e4dd3c3211bf167ba85d13b4e93
                        <input type="hidden" name="action" value="login">
                        <h2>¡Bienvenido!</h2>
                        <p class="subtitle">Ingresa tus datos para continuar</p>
                        
                        <div class="field">
                            <input type="email" name="email" placeholder="Correo electrónico" required>
                        </div>
                        <div class="field">
                            <input type="password" name="password" placeholder="Contraseña" required>
                        </div>
                        <div class="options">
                            <label><input type="checkbox"> Recordarme</label>
<<<<<<< HEAD
                            <a href="recuperacion.php">Olvide mi contraseña</a>
=======
                            <a href="#">Olvide mi contraseña</a>
>>>>>>> 8ad76e6dd18c8e4dd3c3211bf167ba85d13b4e93
                        </div>
                        <button type="submit" class="btn-primary">Entrar</button>
                    </form>

<<<<<<< HEAD
                    <form id="registerForm" action="../src/login.php" method="POST" class="form-pane">
=======
                    <form id="registerForm" action="../backend/login.php" method="POST" class="form-pane">
>>>>>>> 8ad76e6dd18c8e4dd3c3211bf167ba85d13b4e93
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

<?php include '../templates/footer.php'; ?>