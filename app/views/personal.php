<?php
$titulo = "Registro de personal";
$activo = "personal";
require_once __DIR__ . '/../helpers/QrSvg.php';
require __DIR__ . '/partials/encabezado.php';
$e = $editando;
?>
    <div class="contenido">
        <div class="encabezado">
            <h1>Registro de personal</h1>
            <p class="subtitulo">Registra, edita y consulta los datos del personal de la empresa.</p>
        </div>

        <section class="panel">
            <h2><?php echo $e ? 'Editar trabajador' : 'Nuevo trabajador'; ?></h2>

            <?php if ($mensaje != "") { ?>
                <p class="<?php echo $tipoMensaje; ?>" role="status"><?php echo htmlspecialchars($mensaje); ?></p>
            <?php } ?>

            <form action="personal.php" method="POST">
                <?php echo Sesion::campoToken(); ?>
                <?php if ($e) { ?><input type="hidden" name="id_personal" value="<?php echo (int)$e['id_personal']; ?>"><?php } ?>
                <div class="rejilla-form">
                    <div>
                        <label for="nombres">Nombres completos</label>
                        <input type="text" id="nombres" name="nombres" maxlength="100" value="<?php echo $e ? htmlspecialchars($e['nombres']) : ''; ?>" required>
                    </div>
                    <div>
                        <label for="dni">DNI</label>
                        <input type="text" id="dni" name="dni" maxlength="8" inputmode="numeric" pattern="[0-9]{8}" title="8 números" value="<?php echo $e ? htmlspecialchars($e['dni']) : ''; ?>" required>
                    </div>
                    <div>
                        <label for="cargo">Cargo</label>
                        <input type="text" id="cargo" name="cargo" maxlength="60" value="<?php echo $e ? htmlspecialchars($e['cargo']) : ''; ?>" required>
                    </div>
                    <div>
                        <label for="hora_entrada">Hora de entrada</label>
                        <input type="time" id="hora_entrada" name="hora_entrada" value="<?php echo $e ? substr($e['hora_entrada'], 0, 5) : '08:00'; ?>" required>
                    </div>
                </div>
                <p class="gris-txt"><small>Los datos personales se usan solo para el control de asistencia de la empresa, conforme a la Ley N.° 29733 de Protección de Datos Personales.</small></p>
                <div class="acciones-form">
                    <button type="submit"><?php echo $e ? 'Guardar cambios' : 'Registrar'; ?></button>
                    <?php if ($e) { ?><a class="boton-mini boton-claro" href="personal.php">Cancelar</a><?php } ?>
                </div>
            </form>
        </section>

        <section class="panel">
            <div class="panel-cabecera">
                <div>
                    <h2>Personal registrado</h2>
                    <span class="rango">Cada trabajador cuenta con un código QR único para registro rápido de asistencia.</span>
                </div>
                <div class="acciones">
                    <a class="boton-mini" href="fotocheck.php" target="_blank">Imprimir todos los Fotochecks QR</a>
                </div>
            </div>
            <div class="tabla-caja">
            <table>
                <thead>
                    <tr>
                        <th>Trabajador</th>
                        <th>DNI</th>
                        <th>Hora de entrada</th>
                        <th>Código QR</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php $hay = false; while ($fila = $lista->fetch_assoc()) { $hay = true; ?>
                    <tr>
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
                        <td><?php echo substr($fila['hora_entrada'], 0, 5); ?> h</td>
                        <td>
                            <a href="fotocheck.php?id=<?php echo $fila['id_personal']; ?>" target="_blank" class="qr-miniatura" title="Abrir fotocheck QR de <?php echo htmlspecialchars($fila['nombres']); ?>">
                                <?php echo QrSvg::svg('INFORTEC:' . $fila['dni'], 44); ?>
                            </a>
                        </td>
                        <td>
                            <div class="acciones">
                                <a class="boton-mini" href="personal.php?editar=<?php echo $fila['id_personal']; ?>#nombres">Editar</a>
                                <a class="boton-mini boton-claro" href="fotocheck.php?id=<?php echo $fila['id_personal']; ?>" target="_blank">Fotocheck QR</a>
                                <a class="boton-mini boton-claro" href="historial.php?id=<?php echo $fila['id_personal']; ?>">Historial</a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                <?php if (!$hay) { ?>
                    <tr><td colspan="5" class="vacio">Aún no hay personal registrado.</td></tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
        </section>
    </div>
<?php require __DIR__ . '/partials/pie.php'; ?>
