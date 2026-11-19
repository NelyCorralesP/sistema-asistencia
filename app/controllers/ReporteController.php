<?php
require_once __DIR__ . '/../helpers/Sesion.php';
require_once __DIR__ . '/../helpers/Fechas.php';
require_once __DIR__ . '/../models/Personal.php';
require_once __DIR__ . '/../models/Asistencia.php';

class ReporteController
{
    public static function index()
    {
        Sesion::exigir();
        require_once __DIR__ . '/../../config/conexion.php';

        list($desde, $hasta) = Fechas::rango();
        $id = (int)($_GET['id'] ?? 0);
        $estado = $_GET['estado'] ?? '';
        if (!in_array($estado, ['Puntual', 'Tardanza', 'Falta'], true)) {
            $estado = '';
        }

        $personas = Personal::listar($conn)->fetch_all(MYSQLI_ASSOC);
        $filas = Asistencia::consultar($conn, $desde, $hasta, $id, $estado);
        $resumen = Fechas::resumen($filas);
        $porPersonal = Asistencia::resumenPorPersonal($conn, $desde, $hasta, $id);

        if (($_GET['exportar'] ?? '') === 'csv') {
            self::csv($filas, $desde, $hasta);
            exit();
        }

        require __DIR__ . '/../views/reportes.php';
    }

    private static function csv($filas, $desde, $hasta)
    {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="reporte_asistencia_' . $desde . '_a_' . $hasta . '.csv"');
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF"); // BOM para que Excel lea las tildes
        fputcsv($out, ['Fecha', 'Trabajador', 'DNI', 'Cargo', 'Entrada', 'Salida', 'Horas', 'Estado'], ';');
        foreach ($filas as $f) {
            fputcsv($out, [
                $f['fecha'], $f['nombres'], $f['dni'], $f['cargo'],
                $f['hora_entrada'] ? substr($f['hora_entrada'], 0, 5) : '',
                $f['hora_salida'] ? substr($f['hora_salida'], 0, 5) : '',
                $f['horas'] ?? '', $f['estado']
            ], ';');
        }
        fclose($out);
    }
}
