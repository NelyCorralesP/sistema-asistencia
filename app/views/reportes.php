<?php
$titulo = "Reportes de asistencia";
$activo = "reportes";
require __DIR__ . '/partials/encabezado.php';
$qs = http_build_query(['id' => $id, 'desde' => $desde, 'hasta' => $hasta, 'estado' => $estado, 'exportar' => 'csv']);
?>
    <div class="contenido">
        <div class="encabezado">
            <h1>Reportes de asistencia</h1>
            <p class="subtitulo">Genera reportes por rango de fechas o por trabajador, imprímelos o expórtalos a Excel.</p>
        </div>

        <section class="panel sin-imprimir">
            <form action="reportes.php" method="GET" class="filtros">
                <div class="campo-filtro">
                    <label for="id">Trabajador</label>
                    <select id="id" name="id">
                        <option value="0">Todos</option>
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
                <div class="campo-filtro">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado">
                        <option value="">Todos</option>
                        <?php foreach (['Puntual', 'Tardanza', 'Falta'] as $s) { ?>
                            <option value="<?php echo $s; ?>" <?php echo $estado == $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="campo-filtro boton-filtro">
                    <button type="submit">Generar</button>
                </div>
            </form>
        </section>

        <div class="tarjetas">
            <div class="tarjeta"><span class="numero"><?php echo $resumen['total']; ?></span><span class="etiqueta">Registros</span></div>
            <div class="tarjeta verde"><span class="numero"><?php echo $resumen['puntual']; ?></span><span class="etiqueta">Puntuales</span></div>
            <div class="tarjeta ambar"><span class="numero"><?php echo $resumen['tardanza']; ?></span><span class="etiqueta">Tardanzas</span></div>
            <div class="tarjeta roja"><span class="numero"><?php echo $resumen['falta']; ?></span><span class="etiqueta">Faltas</span></div>
            <div class="tarjeta"><span class="numero"><?php echo $resumen['pct']; ?>%</span><span class="etiqueta">Puntualidad</span></div>
        </div>

        <?php
        $totR = max(1, (int)$resumen['total']);
        $pPunt = round($resumen['puntual'] * 100 / $totR);
        $pTard = round($resumen['tardanza'] * 100 / $totR);
        $pFalt = round($resumen['falta'] * 100 / $totR);
        ?>
        <section class="panel">
            <div class="panel-cabecera">
                <div>
                    <h2>Indicadores visuales de cumplimiento</h2>
                    <span class="rango">Distribución porcentual del periodo <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></span>
                </div>
                <span class="etiqueta-estado estado-puntual"><?php echo $resumen['pct']; ?>% Puntualidad General</span>
            </div>
            <div class="barra-apilada" title="Puntuales: <?php echo $pPunt; ?>% | Tardanzas: <?php echo $pTard; ?>% | Faltas: <?php echo $pFalt; ?>%">
                <?php if ($resumen['total'] > 0) { ?>
                    <span class="seg-puntual" style="width: <?php echo $pPunt; ?>%"></span>
                    <span class="seg-tardanza" style="width: <?php echo $pTard; ?>%"></span>
                    <span class="seg-falta" style="width: <?php echo $pFalt; ?>%"></span>
                <?php } ?>
            </div>
            <div class="leyenda-barra">
                <span><i class="punto verde"></i> Puntuales: <strong><?php echo $resumen['puntual']; ?></strong> (<?php echo $resumen['total'] > 0 ? $pPunt : 0; ?>%)</span>
                <span><i class="punto ambar"></i> Tardanzas: <strong><?php echo $resumen['tardanza']; ?></strong> (<?php echo $resumen['total'] > 0 ? $pTard : 0; ?>%)</span>
                <span><i class="punto rojo"></i> Faltas: <strong><?php echo $resumen['falta']; ?></strong> (<?php echo $resumen['total'] > 0 ? $pFalt : 0; ?>%)</span>
            </div>
        </section>

        <section class="panel">
            <div class="panel-cabecera">
                <div>
                    <h2>Resumen por trabajador</h2>
                    <span class="rango">Del <?php echo date('d/m/Y', strtotime($desde)); ?> al <?php echo date('d/m/Y', strtotime($hasta)); ?></span>
                </div>
                <div class="acciones sin-imprimir">
                    <button type="button" class="boton-mini boton-claro" onclick="window.print()">Imprimir / Exportar a PDF</button>
                    <a class="boton-mini" href="reportes.php?<?php echo htmlspecialchars($qs); ?>">Exportar a Excel</a>
                </div>
            </div>
            <div class="tabla-caja">
            <table>
                <thead>
                    <tr><th>Trabajador</th><th>Puntuales</th><th>Tardanzas</th><th>Faltas</th><th>Total</th><th>Cumplimiento</th></tr>
                </thead>
                <tbody>
                <?php if (!$porPersonal) { ?>
                    <tr><td colspan="6" class="vacio">Sin registros en el rango seleccionado.</td></tr>
                <?php } ?>
                <?php foreach ($porPersonal as $r) {
                    $pctInd = $r['total'] > 0 ? round($r['puntuales'] * 100 / $r['total']) : 0;
                ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($r['nombres']); ?></strong> <small class="gris-txt"><?php echo htmlspecialchars($r['cargo']); ?></small></td>
                        <td><?php echo (int)$r['puntuales']; ?></td>
                        <td><?php echo (int)$r['tardanzas']; ?></td>
                        <td><?php echo (int)$r['faltas']; ?></td>
                        <td><?php echo (int)$r['total']; ?></td>
                        <td>
                            <div class="progreso-celda">
                                <div class="progreso-mini"><span style="width: <?php echo $pctInd; ?>%"></span></div>
                                <strong><?php echo $pctInd; ?>%</strong>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
            </div>
        </section>

        <section class="panel">
            <h2>Detalle de asistencias</h2>
            <div class="tabla-caja">
            <table>
                <thead>
                    <tr><th>Fecha</th><th>Trabajador</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Estado</th></tr>
                </thead>
                <tbody>
                <?php if (!$filas) { ?>
                    <tr><td colspan="6" class="vacio">Sin registros en el rango seleccionado.</td></tr>
                <?php } ?>
                <?php foreach ($filas as $f) {
                    $clase = $f['estado'] == 'Puntual' ? 'puntual' : ($f['estado'] == 'Tardanza' ? 'tardanza' : 'falta');
                ?>
                    <tr>
                        <td><?php echo date('d/m/Y', strtotime($f['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($f['nombres']); ?></td>
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
    </div>
<?php require __DIR__ . '/partials/pie.php'; ?>
