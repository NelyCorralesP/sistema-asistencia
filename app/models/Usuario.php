<?php
class Usuario
{
    const MAX_INTENTOS = 5;      // intentos fallidos permitidos
    const MINUTOS_BLOQUEO = 15;  // tiempo de bloqueo tras superar los intentos

    public static function buscarPorUsuario($conn, $usuario)
    {
        $stmt = $conn->prepare("SELECT id_usuario, usuario, clave, nombre, intentos_fallidos, bloqueado_hasta FROM usuarios WHERE usuario = ?");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public static function estaBloqueado($fila)
    {
        return $fila['bloqueado_hasta'] !== null && strtotime($fila['bloqueado_hasta']) > time();
    }

    public static function registrarFallo($conn, $fila)
    {
        $intentos = (int)$fila['intentos_fallidos'] + 1;
        $hasta = null;
        if ($intentos >= self::MAX_INTENTOS) {
            $hasta = date('Y-m-d H:i:s', time() + self::MINUTOS_BLOQUEO * 60);
            $intentos = 0;
        }
        $q = $conn->prepare("UPDATE usuarios SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id_usuario = ?");
        $q->bind_param("isi", $intentos, $hasta, $fila['id_usuario']);
        $q->execute();
        return $hasta !== null;
    }

    public static function reiniciarIntentos($conn, $id_usuario)
    {
        $q = $conn->prepare("UPDATE usuarios SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?");
        $q->bind_param("i", $id_usuario);
        $q->execute();
    }
}
