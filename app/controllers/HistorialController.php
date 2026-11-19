<?php
require_once __DIR__ . '/../helpers/Sesion.php';
require_once __DIR__ . '/../helpers/Fechas.php';
require_once __DIR__ . '/../models/Personal.php';
require_once __DIR__ . '/../models/Asistencia.php';

class HistorialController
{
    public static function index()
    {
        Sesion::exigir();
        require_once __DIR__ . '/../../config/conexion.php';

        list($desde, $hasta) = Fechas::rango();
        $id = (int)($_GET['id'] ?? 0);
        $personas = Personal::listar($conn)->fetch_all(MYSQLI_ASSOC);
        $persona = $id > 0 ? Personal::buscarPorId($conn, $id) : null;

        $filas = [];
        $resumen = null;
        if ($persona) {
            $filas = Asistencia::consultar($conn, $desde, $hasta, $id);
            $resumen = Fechas::resumen($filas);
        }
        require __DIR__ . '/../views/historial.php';
    }
}
