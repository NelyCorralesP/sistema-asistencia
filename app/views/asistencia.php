<?php
$titulo = "Control de asistencia";
$activo = "asistencia";
require __DIR__ . '/partials/encabezado.php';

$filas = $resultado->fetch_all(MYSQLI_ASSOC);
$total = count($filas);
$puntuales = $tardanzas = $faltas = 0;
foreach ($filas as $f) {
    if ($f['estado'] == 'Puntual') $puntuales++;
    if ($f['estado'] == 'Tardanza') $tardanzas++;
    if ($f['estado'] == 'Falta') $faltas++;
}
$pendientes = $total - $puntuales - $tardanzas - $faltas;
?>
    <div class="contenido">
        <div class="encabezado">
            <h1>Asistencia de hoy</h1>
            <p class="subtitulo">
                Bienvenido, <?php echo htmlspecialchars($_SESSION['nombre_completo']); ?> &middot;
                <?php echo date("d/m/Y"); ?>
            </p>
        </div>

        <div class="tarjetas">
            <div class="tarjeta"><span class="numero"><?php echo $total; ?></span><span class="etiqueta">Personal total</span></div>
            <div class="tarjeta verde"><span class="numero"><?php echo $puntuales; ?></span><span class="etiqueta">Puntuales</span></div>
            <div class="tarjeta ambar"><span class="numero"><?php echo $tardanzas; ?></span><span class="etiqueta">Tardanzas</span></div>
            <div class="tarjeta roja"><span class="numero"><?php echo $faltas; ?></span><span class="etiqueta">Faltas</span></div>
            <div class="tarjeta gris"><span class="numero"><?php echo $pendientes; ?></span><span class="etiqueta">Sin marcar</span></div>
        </div>

        <section class="panel">
            <div class="panel-cabecera">
                <div>
                    <h2>Terminal de Registro: Escáner QR y DNI</h2>
                    <span class="rango">Escanee el fotocheck QR con la cámara o pistola lectora, o ingrese el DNI del trabajador.</span>
                </div>
                <div class="reloj-terminal">
                    <span class="reloj-fecha"><?php echo date("d/m/Y"); ?></span>
                    <strong class="reloj-hora" id="relojVivo"><?php echo date("H:i:s"); ?></strong>
                </div>
            </div>

            <?php if ($mensaje != "") { ?>
                <p class="<?php echo $tipoMensaje; ?>" role="status"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php } ?>

            <div class="terminal-grid">
                <!-- Columna 1: Formulario por DNI o Pistola Lectora QR -->
                <div class="terminal-bloque">
                    <form action="asistencia.php" method="POST" id="formTerminal">
                        <?php echo Sesion::campoToken(); ?>
                        <div class="rejilla-form">
                            <div>
                                <label for="dni">Código QR o DNI del trabajador</label>
                                <input type="text" id="dni" name="dni" maxlength="32" placeholder="Escanee QR o digite los 8 números del DNI" autofocus required>
                            </div>
                            <div>
                                <label for="fecha_vista">Fecha</label>
                                <input type="text" id="fecha_vista" value="<?php echo date("d/m/Y"); ?>" readonly>
                            </div>
                            <div>
                                <label for="hora_vista">Hora actual</label>
                                <input type="text" id="hora_vista" value="<?php echo date("H:i"); ?>" readonly>
                            </div>
                        </div>

                        <div class="acciones">
                            <button type="submit" name="accion" value="entrada" class="boton-mini">Registrar entrada</button>
                            <button type="submit" name="accion" value="salida" class="boton-mini boton-salida">Registrar salida</button>
                            <button type="submit" name="accion" value="auto" class="boton-mini boton-claro" id="btnAutoQr" title="Si aún no marcó hoy registra Entrada; si ya tiene Entrada registra Salida">Marcado inteligente QR</button>
                            <a href="fotocheck.php" target="_blank" class="boton-mini boton-claro">Ver Fotochecks QR</a>
                        </div>
                    </form>
                </div>

                <!-- Columna 2: Escáner QR con Cámara Web en vivo -->
                <div class="terminal-camara">
                    <div class="camara-visor" id="visorQr">
                        <video id="videoQr" playsinline muted></video>
                        <div class="camara-marco"></div>
                        <div class="camara-placeholder" id="placeholderQr">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/>
                            </svg>
                            <span id="estadoCamara">Cámara QR en espera</span>
                        </div>
                    </div>
                    <div class="camara-controles">
                        <button type="button" class="boton-mini" id="btnIniciarCamara" onclick="alternarCamaraQr()">Activar cámara QR</button>
                    </div>
                </div>
            </div>
        </section>

        <section class="panel">
            <div class="panel-cabecera">
                <div>
                    <h2>Asistencias de hoy</h2>
                    <span class="rango">Control en tiempo real del personal de Tecnología Infortec</span>
                </div>
                <div class="buscador-tabla">
                    <input type="search" id="buscarHoy" placeholder="Filtrar por nombre, DNI o cargo..." oninput="filtrarTablaHoy(this.value)">
                </div>
            </div>
            <div class="tabla-caja">
            <table id="tablaHoy">
                <thead>
                    <tr>
                        <th>Trabajador</th>
                        <th>DNI</th>
                        <th>Horario</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($total == 0) { ?>
                    <tr><td colspan="7" class="vacio">No hay personal registrado. <a href="personal.php">Registrar personal</a></td></tr>
                <?php } ?>
                <?php foreach ($filas as $fila) {
                    $est = $fila['estado'];
                    $clase = $est == 'Puntual' ? 'puntual' : ($est == 'Tardanza' ? 'tardanza' : ($est == 'Falta' ? 'falta' : 'pendiente'));
                ?>
                    <tr class="fila-hoy">
                        <td>
                            <div class="persona">
                                <span class="avatar"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($fila['nombres'], 0, 1))); ?></span>
                                <span>
                                    <strong><?php echo htmlspecialchars($fila['nombres']); ?></strong>
                                    <small><?php echo htmlspecialchars($fila['cargo']); ?></small>
                                </span>
                            </div>
                        </td>
                        <td><span class="badge-dni"><?php echo htmlspecialchars($fila['dni']); ?></span></td>
                        <td><?php echo substr($fila['horario'], 0, 5); ?> h</td>
                        <td><?php echo $fila['hora_entrada'] ? substr($fila['hora_entrada'], 0, 5) : "-"; ?></td>
                        <td><?php echo $fila['hora_salida'] ? substr($fila['hora_salida'], 0, 5) : "-"; ?></td>
                        <td><span class="etiqueta-estado estado-<?php echo $clase; ?>"><?php echo $est ? htmlspecialchars($est) : "Pendiente"; ?></span></td>
                        <td>
                            <div class="acciones">
                            <?php if ($est == null) { ?>
                                <form action="asistencia.php" method="POST">
                                    <?php echo Sesion::campoToken(); ?>
                                    <input type="hidden" name="id_personal" value="<?php echo $fila['id_personal']; ?>">
                                    <input type="hidden" name="accion" value="entrada">
                                    <button type="submit" class="boton-mini">Marcar entrada</button>
                                </form>
                                <form action="asistencia.php" method="POST" onsubmit="return confirm('¿Registrar falta a este trabajador?');">
                                    <?php echo Sesion::campoToken(); ?>
                                    <input type="hidden" name="id_personal" value="<?php echo $fila['id_personal']; ?>">
                                    <input type="hidden" name="accion" value="falta">
                                    <button type="submit" class="boton-mini boton-peligro">Registrar falta</button>
                                </form>
                            <?php } elseif ($est == 'Falta') { ?>
                                <form action="asistencia.php" method="POST">
                                    <?php echo Sesion::campoToken(); ?>
                                    <input type="hidden" name="id_personal" value="<?php echo $fila['id_personal']; ?>">
                                    <input type="hidden" name="accion" value="anular_falta">
                                    <button type="submit" class="boton-mini boton-claro">Anular falta</button>
                                </form>
                            <?php } elseif ($fila['hora_salida'] == null) { ?>
                                <form action="asistencia.php" method="POST">
                                    <?php echo Sesion::campoToken(); ?>
                                    <input type="hidden" name="id_personal" value="<?php echo $fila['id_personal']; ?>">
                                    <input type="hidden" name="accion" value="salida">
                                    <button type="submit" class="boton-mini boton-salida">Marcar salida</button>
                                </form>
                            <?php } else { ?>
                                <span class="completo">&#10003; Completo</span>
                            <?php } ?>
                                <a class="boton-mini boton-claro" href="fotocheck.php?id=<?php echo $fila['id_personal']; ?>" target="_blank" title="Ver Fotocheck QR">QR</a>
                                <a class="boton-mini boton-claro" href="historial.php?id=<?php echo $fila['id_personal']; ?>">Historial</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
        </section>
    </div>

    <script>
    // Reloj en vivo sincronizado cada segundo
    (function () {
        var el = document.getElementById('relojVivo');
        var hv = document.getElementById('hora_vista');
        setInterval(function () {
            var d = new Date();
            var hh = String(d.getHours()).padStart(2, '0');
            var mm = String(d.getMinutes()).padStart(2, '0');
            var ss = String(d.getSeconds()).padStart(2, '0');
            if (el) el.textContent = hh + ':' + mm + ':' + ss;
            if (hv) hv.value = hh + ':' + mm;
        }, 1000);

        // Si el usuario o una pistola lectora escanea "INFORTEC:12345678", enviarlo en modo inteligente
        var inputDni = document.getElementById('dni');
        if (inputDni) {
            inputDni.addEventListener('input', function () {
                var val = this.value.trim();
                if (/^INFORTEC:\d{8}$/i.test(val)) {
                    document.getElementById('btnAutoQr').click();
                }
            });
        }
    })();

    // Filtro rapido en la tabla de asistencias de hoy
    function filtrarTablaHoy(texto) {
        var q = texto.toLowerCase().trim();
        var filas = document.querySelectorAll('#tablaHoy tbody tr.fila-hoy');
        filas.forEach(function (tr) {
            tr.style.display = tr.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
        });
    }

    // Escaner QR con camara web (BarcodeDetector nativo de Chrome / Edge)
    var streamQr = null;
    var intervaloQr = null;

    async function alternarCamaraQr() {
        var video = document.getElementById('videoQr');
        var btn = document.getElementById('btnIniciarCamara');
        var estado = document.getElementById('estadoCamara');
        var placeholder = document.getElementById('placeholderQr');

        if (streamQr) {
            detenerCamaraQr();
            return;
        }

        try {
            streamQr = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = streamQr;
            await video.play();
            video.classList.add('activo');
            placeholder.style.display = 'none';
            btn.textContent = 'Detener cámara QR';
            btn.classList.add('boton-peligro');

            if ('BarcodeDetector' in window) {
                var detector = new BarcodeDetector({ formats: ['qr_code'] });
                intervaloQr = setInterval(async function () {
                    if (video.readyState === video.HAVE_ENOUGH_DATA) {
                        try {
                            var codigos = await detector.detect(video);
                            if (codigos.length > 0) {
                                var valor = codigos[0].rawValue;
                                detenerCamaraQr();
                                document.getElementById('dni').value = valor;
                                document.getElementById('btnAutoQr').click();
                            }
                        } catch (e) {}
                    }
                }, 300);
            } else {
                estado.textContent = 'Cámara activa (use Chrome/Edge o lector USB para detección automática)';
            }
        } catch (err) {
            estado.textContent = 'No se pudo acceder a la cámara. Use el lector o ingrese el DNI.';
        }
    }

    function detenerCamaraQr() {
        var video = document.getElementById('videoQr');
        var btn = document.getElementById('btnIniciarCamara');
        var placeholder = document.getElementById('placeholderQr');
        var estado = document.getElementById('estadoCamara');
        if (intervaloQr) { clearInterval(intervaloQr); intervaloQr = null; }
        if (streamQr) {
            streamQr.getTracks().forEach(function (t) { t.stop(); });
            streamQr = null;
        }
        video.classList.remove('activo');
        placeholder.style.display = 'flex';
        estado.textContent = 'Cámara QR en espera';
        btn.textContent = 'Activar cámara QR';
        btn.classList.remove('boton-peligro');
    }
    </script>
<?php require __DIR__ . '/partials/pie.php'; ?>
