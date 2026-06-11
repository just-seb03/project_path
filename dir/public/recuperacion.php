<?php include '../templates/header.php'; ?>

<div class="login-wrapper">
    <div class="login-box" style="width: 500px; height: auto; min-height: 400px; display: flex; flex-direction: column;">
        <div class="side-form" style="width: 100%; border-right: none; padding: 40px; border-radius: 24px;">
            <div class="forms-container" style="padding-right: 0;">
                <div class="form-slider" id="recoverySlider">
                    
                    <form id="recoverStep1" action="../src/recuperacion.php" method="POST" class="form-pane active">
                        <input type="hidden" name="action" value="solicitar">
                        <h2>Recuperar Contraseña</h2>
                        <p class="subtitle">Ingresa tu correo para recibir un código de recuperación</p>
                        
                        <div class="field">
                            <input type="email" name="email" id="recoveryEmail" placeholder="Correo electrónico" required>
                        </div>
                        
                        <button type="submit" class="btn-primary">Enviar Código</button>
                        
                        <div class="options" style="margin-top: 20px; justify-content: center;">
                            <a href="login.php?mode=login">Volver al inicio de sesión</a>
                        </div>
                    </form>

                    <form id="recoverStep2" action="../src/recuperacion.php" method="POST" class="form-pane">
                        <input type="hidden" name="action" value="verificar">
                        <input type="hidden" name="email" id="hiddenEmail">
                        <h2>Código Enviado</h2>
                        <p class="subtitle">Ingresa el código de 6 dígitos y tu nueva contraseña</p>
                        
                        <div class="field">
                            <input type="text" name="codigo" placeholder="Código de 6 dígitos" required maxlength="6">
                        </div>
                        <div class="field">
                            <input type="password" name="nueva_password" placeholder="Nueva Contraseña" required>
                        </div>
                        
                        <button type="submit" class="btn-primary">Cambiar Contraseña</button>
                        
                        <div class="options" style="margin-top: 20px; justify-content: center;">
                            <a href="login.php?mode=login">Cancelar</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/tmp.js"></script>
<?php include '../templates/footer.php'; ?>