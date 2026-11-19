<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Tecnología Infortec</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css?v=<?php echo filemtime(__DIR__ . '/../../css/estilo.css'); ?>">
</head>
<body class="login-pagina">
    <div class="login-contenedor">
        <main class="login-caja">
            <div class="login-cabecera">
                <div class="login-logo-contenedor">
                    <img src="img/logo.png" alt="Logo Tecnología Infortec" class="logo" onerror="this.style.display='none'">
                </div>
                <h1>Tecnología Infortec</h1>
                <p class="subtitulo">Control de Asistencia y Personal</p>
            </div>

            <?php if ($hayError) { ?>
                <div class="error" role="alert">
                    <svg class="alerta-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <span><?php echo htmlspecialchars($mensajeError); ?></span>
                </div>
            <?php } ?>

            <form action="login.php" method="POST" autocomplete="on">
                <?php echo Sesion::campoToken(); ?>
                <div class="campo-grupo">
                    <label for="usuario">Usuario del sistema</label>
                    <input type="text" id="usuario" name="usuario" placeholder="Ingrese su usuario" autocomplete="username" autofocus required>
                </div>

                <div class="campo-grupo">
                    <label for="password">Contraseña</label>
                    <div class="campo-clave">
                        <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                        <button type="button" class="ver-clave" onclick="var c=document.getElementById('password');var m=c.type==='password';c.type=m?'text':'password';this.textContent=m?'Ocultar':'Ver';">Ver</button>
                    </div>
                </div>

                <button type="submit" class="btn-login">Iniciar sesión</button>
            </form>

            <div class="pie-login">
                <span>&copy; <?php echo date("Y"); ?> Tecnología Infortec &middot; Nueva Cajamarca</span>
            </div>
        </main>
    </div>
</body>
</html>
