<?php
class Fechas
{
    // Devuelve una fecha Y-m-d válida o el valor por defecto
    public static function valida($valor, $defecto)
    {
        $d = DateTime::createFromFormat('Y-m-d', (string)$valor);
        return ($d && $d->format('Y-m-d') === $valor) ? $valor : $defecto;
    }

    // Rango de fechas desde $_GET (por defecto: mes actual) con desde <= hasta
    public static function rango()
    {
        date_default_timezone_set('America/Lima');
        $desde = self::valida($_GET['desde'] ?? '', date('Y-m-01'));
        $hasta = self::valida($_GET['hasta'] ?? '', date('Y-m-d'));
        if ($desde > $hasta) {
            list($desde, $hasta) = [$hasta, $desde];
        }
        return [$desde, $hasta];
    }

    public static function resumen($filas)
    {
        $r = ['puntual' => 0, 'tardanza' => 0, 'falta' => 0, 'total' => count($filas)];
        foreach ($filas as $f) {
            if ($f['estado'] == 'Puntual') $r['puntual']++;
            if ($f['estado'] == 'Tardanza') $r['tardanza']++;
            if ($f['estado'] == 'Falta') $r['falta']++;
        }
        $r['pct'] = $r['total'] > 0 ? round($r['puntual'] * 100 / $r['total']) : 0;
        return $r;
    }
}
