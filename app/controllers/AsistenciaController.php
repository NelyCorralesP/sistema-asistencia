<?php
require_once __DIR__ . '/../helpers/Sesion.php';
require_once __DIR__ . '/../models/Personal.php';
require_once __DIR__ . '/../models/Asistencia.php';

class AsistenciaController
{
    public static function index()
    {
        Sesion::exigir();
        require_once __DIR__ . '/../../config/conexion.php';

        date_default_timezone_set('America/Lima');
        $hoy = date("Y-m-d");
        $ahora = date("H:i:s");
        $mensaje = "";
        $tipoMensaje = "aviso";

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $id_personal = (int)($_POST['id_personal'] ?? 0);
            $dni_buscado = trim($_POST['dni'] ?? '');
            $accion = $_POST['accion'] ?? '';
            $personaInfo = null;

            // Extraer DNI de 8 digitos si viene de un codigo QR (ej. INFORTEC:72345678)
            if ($dni_buscado !== '' && preg_match('/\b(\d{8})\b/', $dni_buscado, $coincidencia)) {
                $dni_buscado = $coincidencia[1];
            }

            if (!Sesion::tokenValido()) {
                $mensaje = "La página expiró. Vuelva a intentarlo.";
                $tipoMensaje = "error";
                $accion = '';
            } elseif ($dni_buscado !== '' && $id_personal === 0) {
                $encontrado = Personal::buscarPorDni($conn, $dni_buscado);
                if ($encontrado) {
                    $id_personal = (int)$encontrado['id_personal'];
                    $personaInfo = $encontrado;
                } else {
                    $mensaje = "No se encontró ningún trabajador con el código o DNI escaneado (" . $dni_buscado . ").";
                    $tipoMensaje = "error";
                    $accion = '';
                }
            }

            if ($id_personal > 0 && !$personaInfo) {
                $personaInfo = Personal::buscarPorId($conn, $id_personal);
            }

            $reg = $id_personal > 0 ? Asistencia::delDia($conn, $id_personal, $hoy) : null;

            // Modo inteligente "auto" para escaneo QR: si no tiene marca hoy -> entrada; si ya tiene entrada sin salida -> salida
            if ($accion === 'auto' && $personaInfo) {
                if (!$reg) {
                    $accion = 'entrada';
                } elseif ($reg['estado'] === 'Falta') {
                    $mensaje = $personaInfo['nombres'] . " tiene una falta registrada hoy. Anule la falta primero.";
                    $tipoMensaje = "error";
                    $accion = '';
                } elseif ($reg['hora_salida'] === null) {
                    $accion = 'salida';
                } else {
                    $mensaje = $personaInfo['nombres'] . " ya completó su entrada y salida de hoy.";
                    $accion = '';
                }
            }

            if ($accion !== '' && (!in_array($accion, ['entrada', 'salida', 'falta', 'anular_falta'], true) || !$personaInfo)) {
                $mensaje = "Solicitud no válida.";
                $tipoMensaje = "error";
                $accion = '';
            }

            $etiquetaPersona = $personaInfo ? " (" . $personaInfo['nombres'] . ")" : "";

            if ($accion == "entrada") {
                $persona = Personal::horaEntrada($conn, $id_personal);

                if ($reg && $reg['estado'] == 'Falta') {
                    $mensaje = "Tiene una falta registrada hoy" . $etiquetaPersona . ". Anule la falta para marcar la entrada.";
                    $tipoMensaje = "error";
                } elseif ($reg) {
                    $mensaje = "Ya se marcó la entrada de hoy" . $etiquetaPersona . ".";
                } elseif ($persona) {
                    $estado = ($ahora <= $persona['hora_entrada']) ? "Puntual" : "Tardanza";
                    Asistencia::registrarEntrada($conn, $id_personal, $hoy, $ahora, $estado);
                    $mensaje = "Entrada registrada a las " . substr($ahora, 0, 5) . " — " . $estado . $etiquetaPersona;
                }
            }

            if ($accion == "salida") {
                if (!$reg) {
                    $mensaje = "Primero debe marcar la entrada" . $etiquetaPersona . ".";
                } elseif ($reg['estado'] == 'Falta') {
                    $mensaje = "Tiene una falta registrada hoy, no puede marcar salida" . $etiquetaPersona . ".";
                    $tipoMensaje = "error";
                } elseif ($reg['hora_salida'] != null) {
                    $mensaje = "Ya se marcó la salida de hoy" . $etiquetaPersona . ".";
                } else {
                    Asistencia::registrarSalida($conn, $reg['id_asistencia'], $ahora);
                    $mensaje = "Salida registrada a las " . substr($ahora, 0, 5) . $etiquetaPersona . ".";
                }
            }

            if ($accion == "falta") {
                if ($reg) {
                    $mensaje = "Ya existe un registro de hoy para este trabajador" . $etiquetaPersona . ".";
                    $tipoMensaje = "error";
                } else {
                    Asistencia::registrarFalta($conn, $id_personal, $hoy);
                    $mensaje = "Falta registrada" . $etiquetaPersona . ".";
                }
            }

            if ($accion == "anular_falta") {
                if ($reg && $reg['estado'] == 'Falta') {
                    Asistencia::anularFalta($conn, $reg['id_asistencia']);
                    $mensaje = "Falta anulada" . $etiquetaPersona . ".";
                }
            }

            // Patrón POST-Redirect-GET: evita que al recargar la página se reenvíe la acción
            $_SESSION['flash'] = [$mensaje, $tipoMensaje];
            header("Location: asistencia.php");
            exit();
        }

        if (isset($_SESSION['flash'])) {
            list($mensaje, $tipoMensaje) = $_SESSION['flash'];
            unset($_SESSION['flash']);
        }

        $resultado = Asistencia::listaDelDia($conn, $hoy);
        require __DIR__ . '/../views/asistencia.php';
    }
}
