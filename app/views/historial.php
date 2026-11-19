<?php
$titulo = "Historial de asistencia";
$activo = "historial";
require __DIR__ . '/partials/encabezado.php';
?>
    <div class="contenido">
        <div class="encabezado">
            <h1>Historial de asistencia</h1>
            <p class="subtitulo">Consulta los antecedentes de asistencia de cada trabajador.</p>
        </div>

        <section class="panel sin-imprimir">
            <form action="historial.php" method="GET" class="filtros">
                <div class="campo-filtro">
                    <label for="id">Trabajador</label>
                    <select id="id" name="id" required>
                        <option value="">Seleccione...</option>
                        <?php foreach ($personas as $p) { ?>
                            <option value="<?php echo $p['id_personal']; ?>" <?php echo $p['id_personal'] == $id ? 'selected' : ''; ?>><?php echo htmlspecialchars($p['nombres']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="campo-filtro">
                    <label for="desde">Desde</label>
                    <input type="date" id="desde" name="desde" value="<?php echo $desde; ?>">
                </div>
                <div class="campo-filtro">
                    <label for="hasta">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="<?php echo $hasta; ?>">
                </div>
                <div class="campo-filtro boton-filtro">
                    <button type="submit">Consultar</button>
                </div>
            </form>
        </section>

        <?php if (!$persona) { ?>
            <section class="panel">
                <p class="vacio">Seleccione un trabajador para ver su historial.</p>
            </section>
        <?php } else { ?>
            <div class="tarjetas">
                <div class="tarjeta"><span class="numero"><?php echo $resumen['total']; ?></span><span class="etiqueta">Registros</span></div>
                <div class="tarjeta verde"><span class="numero"><?php echo $resumen['puntual']; ?></span><span class="etiqueta">Puntuales</span></div>
                <div class="tarjeta ambar"><span class="numero"><?php echo $resumen['tardanza']; ?></span><span class="etiqueta">Tardanzas</span></div>
                <div class="tarjeta roja"><span class="numero"><?php echo $resumen['falta']; ?></span><span class="etiqueta">Faltas</span></div>
                <div class="tarjeta"><span class="numero"><?php echo $resumen['pct']; ?>%</span><span class="etiqueta">Puntualidad</span></div>
            </div>

            <section class="panel">
                <div class="panel-cabecera">
                    <div class="persona">
                        <span class="avatar grande"><?php echo htmlspecialchars(mb_strtoupper(mb_substr($persona['nombres'], 0, 1))); ?></span>
                        <span>
                            <strong><?php echo htmlspecialchars($persona['nombres']); ?></strong>
                            <small><?php echo htmlspecialchars($persona['cargo']); ?> &middot; DNI <?php echo htmlspecialchars($persona['dni']); ?> &middot; Horario <?php echo substr($persona['hora_entrada'], 0, 5); ?></small>
                        </span>
                    </div>
                    <span class="rango">Del <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></span>
                </div>
                <div class="tabla-caja">
                <table>
                    <thead>
                        <tr><th>Fecha</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                    <?php if (!$filas) { ?>
                        <tr><td colspan="5" class="vacio">Sin registros en el rango seleccionado.</td></tr>
                    <?php } ?>
                    <?php foreach ($filas as $f) {
                        $clase = $f['estado'] == 'Puntual' ? 'puntual' : ($f['estado'] == 'Tardanza' ? 'tardanza' : 'falta');
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($f['fecha'])); ?></td>
                            <td><?php echo $f['hora_entrada'] ? substr($f['hora_entrada'], 0, 5) : '-'; ?></td>
                            <td><?php echo $f['hora_salida'] ? substr($f['hora_salida'], 0, 5) : '-'; ?></td>
                            <td><?php echo $f['horas'] ? $f['horas'] : '-'; ?></td>
                            <td><span class="etiqueta-estado estado-<?php echo $clase; ?>"><?php echo htmlspecialchars($f['estado']); ?></span></td>
                        </tr>
                    <?php } ?>
                    </tbody>
                </table>
                </div>
            </section>
        <?php } ?>
    </div>
<?php require __DIR__ . '/partials/pie.php'; ?>
