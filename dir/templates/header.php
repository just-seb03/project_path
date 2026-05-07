<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>project_path</title>
    <link rel="stylesheet" href="../public/assets/css/public.css">
</head>
<body>
    <video autoplay muted loop id="bg-video">
        <source src="../public/assets/vid/background.mp4" type="video/mp4">
    </video>

    <div class="bg-decoration">
        <div class="shape blob1"></div>
        <div class="shape blob2"></div>
        <div class="shape blob3"></div>
    </div>

    <header class="main-header">
        <nav class="nav-container">
            <ul class="nav-links">
                <li><a href="inicio.php">Inicio</a></li>
                <li class="dropdown">
                    <a href="#">Ayuda</a>
                    <ul class="dropdown-menu">
                        <li><a href="details.php">¿Qué es PATH?</a></li>
                        <li><a href="changelog.php">Historial de Cambios</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Cuenta</a>
                    <ul class="dropdown-menu">
                        <li><a href="login.php?mode=login">Iniciar sesión</a></li>
                        <li><a href="login.php?mode=register">Registrarse</a></li>
                    </ul>
                </li>
                <li class="dropdown">
                    <a href="#">Sobre el proyecto</a>
                    <ul class="dropdown-menu">
                        <li><a href="integrantes.php">Integrantes</a></li>
                        <li><a href="https://github.com/just-seb03/project_path" target="_blank">GitHub</a></li>
                    </ul>
                </li>
            </ul>
        </nav>
    </header>

    <main id="app-content">