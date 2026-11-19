<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($titulo); ?> | Tecnología Infortec</title>
    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css?v=<?php echo filemtime(__DIR__ . '/../../../css/estilo.css'); ?>">
    <script>
        // Ruta protegida: si el navegador restaura la página desde caché (botón Atrás), se vuelve a pedir al servidor
        window.addEventListener('pageshow', function (e) {
            var nav = performance.getEntriesByType('navigation')[0];
            if (e.persisted || (nav && nav.type === 'back_forward')) {
                window.location.reload();
            }
        });
        window.addEventListener('unload', function () {});
    </script>
</head>
<body class="pagina">
<div class="layout-app">
    <!-- Fondo oscuro al abrir menú en móviles -->
    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="document.body.classList.remove('sidebar-abierto')"></div>

    <!-- Barra lateral (Sidebar tipo SaaS) -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-cabecera">
            <a href="asistencia.php" class="sidebar-marca">
                <div class="marca-icono">
                    <img src="img/logo.png" alt="Tecnología Infortec" onerror="this.style.display='none'">
                </div>
                <div class="marca-texto">
                    <span class="marca-nombre">Tecnología Infortec</span>
                    <span class="marca-sub">Control de Asistencia</span>
                </div>
            </a>
            <button type="button" class="btn-cerrar-sidebar" onclick="document.body.classList.remove('sidebar-abierto')" aria-label="Cerrar menú">&times;</button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-seccion-titulo">Módulos</div>

            <a href="asistencia.php" class="nav-item <?php echo $activo == 'asistencia' ? 'activo' : ''; ?>" onclick="document.body.classList.remove('sidebar-abierto')">
                <svg class="nav-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                    <path d="m9 16 2 2 4-4"/>
                </svg>
                <span>Asistencia de Hoy</span>
            </a>

            <a href="personal.php" class="nav-item <?php echo $activo == 'personal' ? 'activo' : ''; ?>" onclick="document.body.classList.remove('sidebar-abierto')">
                <svg class="nav-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                <span>Personal</span>
            </a>

            <a href="historial.php" class="nav-item <?php echo $activo == 'historial' ? 'activo' : ''; ?>" onclick="document.body.classList.remove('sidebar-abierto')">
                <svg class="nav-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                <span>Historial</span>
            </a>

            <a href="reportes.php" class="nav-item <?php echo $activo == 'reportes' ? 'activo' : ''; ?>" onclick="document.body.classList.remove('sidebar-abierto')">
                <svg class="nav-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="20" x2="18" y2="10"/>
                    <line x1="12" y1="20" x2="12" y2="4"/>
                    <line x1="6" y1="20" x2="6" y2="14"/>
                </svg>
                <span>Reportes</span>
            </a>

            <a href="fotocheck.php" target="_blank" class="nav-item" onclick="document.body.classList.remove('sidebar-abierto')">
                <svg class="nav-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                </svg>
                <span>Fotochecks QR</span>
            </a>
        </nav>

        <div class="sidebar-pie">
            <div class="usuario-perfil">
                <span class="avatar-sm"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($_SESSION['nombre_completo'] ?? 'A', 0, 1))); ?></span>
                <div class="usuario-datos">
                    <strong class="usuario-nombre" title="<?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?>">
                        <?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? 'Usuario'); ?>
                    </strong>
                    <span class="usuario-rol">Administrador</span>
                </div>
            </div>
            <a href="logout.php" class="btn-logout" title="Cerrar sesión">
                <svg class="logout-icono" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                <span>Salir</span>
            </a>
        </div>
    </aside>

    <!-- Contenedor Principal -->
    <div class="layout-principal">
        <!-- Topbar superior -->
        <header class="topbar sin-imprimir">
            <div class="topbar-izq">
                <button type="button" class="btn-hamburguesa" onclick="document.body.classList.toggle('sidebar-abierto')" aria-label="Abrir menú">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="topbar-breadcrumb">
                    <span class="breadcrumb-raiz">Tecnología Infortec</span>
                    <span class="breadcrumb-sep">/</span>
                    <span class="breadcrumb-activo"><?php echo htmlspecialchars($titulo); ?></span>
                </div>
            </div>

            <div class="topbar-der">
                <div class="fecha-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    <span><?php echo date("d/m/Y"); ?></span>
                </div>
                <div class="usuario-chip-top" title="<?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? ''); ?>">
                    <span class="avatar-mini"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($_SESSION['nombre_completo'] ?? 'A', 0, 1))); ?></span>
                    <span class="nombre-chip"><?php echo htmlspecialchars($_SESSION['nombre_completo'] ?? ''); ?></span>
                </div>
            </div>
        </header>

        <main class="layout-cuerpo">
