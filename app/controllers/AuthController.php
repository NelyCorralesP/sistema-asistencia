<?php
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../helpers/Sesion.php';

class AuthController
{
    public static function formulario()
    {
        Sesion::redirigirSiActiva();
        $mensajes = [
            '1' => 'Usuario o contraseña incorrectos',
            'bloqueo' => 'Cuenta bloqueada por varios intentos fallidos. Intente nuevamente en ' . Usuario::MINUTOS_BLOQUEO . ' minutos.',
            'token' => 'La sesión del formulario expiró. Vuelva a intentarlo.',
        ];
        $error = $_GET['error'] ?? '';
        $mensajeError = $mensajes[$error] ?? '';
        $hayError = $mensajeError !== '';
        require __DIR__ . '/../views/login.php';
    }

    public static function login()
    {
        Sesion::redirigirSiActiva();
        require_once __DIR__ . '/../../config/conexion.php';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (!Sesion::tokenValido()) {
                header("Location: index.php?error=token");
                exit();
            }
            $usuario = trim($_POST['usuario'] ?? '');
            $password = trim($_POST['password'] ?? '');

            if ($fila = Usuario::buscarPorUsuario($conn, $usuario)) {
                if (Usuario::estaBloqueado($fila)) {
                    header("Location: index.php?error=bloqueo");
                    exit();
                }
                if (password_verify($password, $fila['clave'])) {
                    Usuario::reiniciarIntentos($conn, $fila['id_usuario']);
                    session_regenerate_id(true);
                    $_SESSION['id_usuario'] = $fila['id_usuario'];
                    $_SESSION['usuario'] = $fila['usuario'];
                    $_SESSION['nombre_completo'] = $fila['nombre'];
                    header("Location: asistencia.php");
                    exit();
                }
                if (Usuario::registrarFallo($conn, $fila)) {
                    header("Location: index.php?error=bloqueo");
                    exit();
                }
            }
            header("Location: index.php?error=1");
            exit();
        }
        header("Location: index.php");
        exit();
    }

    public static function logout()
    {
        Sesion::iniciar();
        Sesion::sinCache();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $k = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $k['path'], $k['domain'], $k['secure'], $k['httponly']);
        }
        session_destroy();
        header("Location: index.php");
        exit();
    }
}

