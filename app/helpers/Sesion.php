<?php
class Sesion
{
    public static function iniciar()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function activa()
    {
        return isset($_SESSION['id_usuario']);
    }

    // Evita que el navegador guarde la página en caché (botón Atrás)
    public static function sinCache()
    {
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");
        header("Expires: 0");
    }

    // Para páginas protegidas
    public static function exigir()
    {
        self::iniciar();
        self::sinCache();
        if (!self::activa()) {
            header("Location: index.php");
            exit();
        }
    }

    // Token contra envíos de formularios desde otros sitios (CSRF)
    public static function token()
    {
        self::iniciar();
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function campoToken()
    {
        return '<input type="hidden" name="csrf" value="' . self::token() . '">';
    }

    public static function tokenValido()
    {
        self::iniciar();
        return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string)$_POST['csrf']);
    }

    // Para el login: si ya hay sesión, ir al panel
    public static function redirigirSiActiva()
    {
        self::iniciar();
        self::sinCache();
        if (self::activa()) {
            header("Location: asistencia.php");
            exit();
        }
    }
}
