<?php
require_once __DIR__ . '/../helpers/Sesion.php';
require_once __DIR__ . '/../models/Personal.php';

class PersonalController
{
    public static function index()
    {
        Sesion::exigir();
        require_once __DIR__ . '/../../config/conexion.php';

        $mensaje = "";
        $tipoMensaje = "aviso";
        $editando = null;   // datos del trabajador en edición

        // Abrir formulario de edición
        if (isset($_GET['editar'])) {
            $editando = Personal::buscarPorId($conn, (int)$_GET['editar']);
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id = (int)($_POST['id_personal'] ?? 0);
            $nombres = trim($_POST['nombres'] ?? '');
            $dni = trim($_POST['dni'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');
            $hora_entrada = $_POST['hora_entrada'] ?? '';

            if ($id > 0) {
                $editando = ['id_personal' => $id, 'nombres' => $nombres, 'dni' => $dni, 'cargo' => $cargo, 'hora_entrada' => $hora_entrada];
            }

            if (!Sesion::tokenValido()) {
                $mensaje = "La página expiró. Vuelva a intentarlo.";
                $tipoMensaje = "error";
            } elseif ($nombres === '' || mb_strlen($nombres) > 100) {
                $mensaje = "Ingrese los nombres completos (máximo 100 caracteres).";
                $tipoMensaje = "error";
            } elseif ($cargo === '' || mb_strlen($cargo) > 60) {
                $mensaje = "Ingrese el cargo (máximo 60 caracteres).";
                $tipoMensaje = "error";
            } elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d(:[0-5]\d)?$/', $hora_entrada)) {
                $mensaje = "La hora de entrada no es válida.";
                $tipoMensaje = "error";
            } elseif (strlen($dni) != 8 || !ctype_digit($dni)) {
                $mensaje = "El DNI debe tener 8 números.";
                $tipoMensaje = "error";
            } elseif (Personal::buscarPorDni($conn, $dni, $id)) {
                $mensaje = "Ese DNI ya está registrado.";
                $tipoMensaje = "error";
            } elseif ($id > 0) {
                Personal::actualizar($conn, $id, $nombres, $dni, $cargo, $hora_entrada);
                $mensaje = "Datos del personal actualizados correctamente.";
                $editando = null;
            } else {
                Personal::crear($conn, $nombres, $dni, $cargo, $hora_entrada);
                $mensaje = "Personal registrado correctamente.";
            }
        }

        $lista = Personal::listar($conn);
        require __DIR__ . '/../views/personal.php';
    }
}
