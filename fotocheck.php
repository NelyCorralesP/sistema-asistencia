<?php
require_once __DIR__ . '/app/helpers/Sesion.php';
require_once __DIR__ . '/app/helpers/QrSvg.php';
require_once __DIR__ . '/app/models/Personal.php';

Sesion::exigir();
require_once __DIR__ . '/config/conexion.php';

$id = (int)($_GET['id'] ?? 0);
$personas = [];
if ($id > 0) {
    $p = Personal::buscarPorId($conn, $id);
    if ($p) {
        $personas[] = $p;
    }
} else {
    $personas = Personal::listar($conn)->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fotochecks QR | Tecnología Infortec</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/estilo.css?v=<?php echo filemtime(__DIR__ . '/css/estilo.css'); ?>">
</head>
<body class="fotocheck-pagina">
    <header class="fotocheck-barra sin-imprimir">
        <div>
            <h1>Fotochecks Institucionales con Código QR</h1>
            <p>Tecnología Infortec E.I.R.L. &middot; Nueva Cajamarca</p>
        </div>
        <div class="acciones">
            <a href="personal.php" class="boton-mini boton-claro">&larr; Volver a Personal</a>
            <?php if ($id > 0) { ?>
                <a href="fotocheck.php" class="boton-mini boton-claro">Ver todos los fotochecks</a>
            <?php } ?>
            <button type="button" class="boton-mini" onclick="window.print()">Imprimir / Guardar PDF</button>
        </div>
    </header>

    <main class="fotocheck-rejilla">
        <?php if (!$personas) { ?>
            <p class="vacio">No se encontraron trabajadores registrados.</p>
        <?php } ?>
        <?php foreach ($personas as $p) {
            $codigoQr = 'INFORTEC:' . $p['dni'];
        ?>
            <article class="carnet">
                <div class="carnet-top">
                    <div class="carnet-logo">
                        <img src="img/logo.png" alt="Logo" onerror="this.style.display='none'">
                    </div>
                    <div class="carnet-empresa">
                        <strong>TECNOLOGÍA INFORTEC</strong>
                        <span>Nueva Cajamarca &middot; San Martín</span>
                    </div>
                </div>

                <div class="carnet-cuerpo">
                    <div class="carnet-avatar">
                        <?php echo htmlspecialchars(mb_strtoupper(mb_substr($p['nombres'], 0, 1))); ?>
                    </div>
                    <h2 class="carnet-nombre"><?php echo htmlspecialchars($p['nombres']); ?></h2>
                    <span class="carnet-cargo"><?php echo htmlspecialchars($p['cargo']); ?></span>

                    <div class="carnet-qr">
                        <?php echo QrSvg::svg($codigoQr, 138); ?>
                    </div>

                    <div class="carnet-datos">
                        <div>
                            <small>DNI</small>
                            <strong><?php echo htmlspecialchars($p['dni']); ?></strong>
                        </div>
                        <div>
                            <small>INGRESO</small>
                            <strong><?php echo substr($p['hora_entrada'], 0, 5); ?> h</strong>
                        </div>
                    </div>
                </div>

                <div class="carnet-pie">
                    <span>CREDENCIAL DE ASISTENCIA &middot; <?php echo htmlspecialchars($codigoQr); ?></span>
                </div>
            </article>
        <?php } ?>
    </main>
</body>
</html>
