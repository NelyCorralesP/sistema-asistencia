<?php
// Herramienta de consola para verificar o restablecer la contraseña de un usuario.
// Solo funciona desde la terminal (no desde el navegador).
//
// Verificar:    c:\xampp\php\php.exe herramientas\clave.php verificar infortec
// Restablecer:  c:\xampp\php\php.exe herramientas\clave.php restablecer infortec
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Acceso denegado');
}

require __DIR__ . '/../config/conexion.php';

$accion  = $argv[1] ?? '';
$usuario = $argv[2] ?? '';
if (!in_array($accion, ['verificar', 'restablecer'], true) || $usuario === '') {
    exit("Uso: php herramientas/clave.php verificar|restablecer <usuario>\n");
}

$s = $conn->prepare("SELECT id_usuario, clave FROM usuarios WHERE usuario = ?");
$s->bind_param("s", $usuario);
$s->execute();
$fila = $s->get_result()->fetch_assoc();
if (!$fila) {
    exit("El usuario '$usuario' no existe.\n");
}

echo ($accion === 'verificar' ? "Contraseña a verificar: " : "Nueva contraseña: ");
$clave = rtrim(fgets(STDIN), "\r\n");

if ($accion === 'verificar') {
    if (password_verify(trim($clave), $fila['clave'])) {
        echo "CORRECTA: esa contraseña coincide con la guardada.\n";
    } elseif (password_verify($clave, $fila['clave'])) {
        echo "Coincide solo con espacios al inicio/final; el login los elimina.\n";
    } else {
        echo "NO coincide: la contraseña guardada en la base de datos es otra.\n";
    }
    exit;
}

$clave = trim($clave);
if (strlen($clave) < 6) {
    exit("La contraseña debe tener al menos 6 caracteres.\n");
}
$hash = password_hash($clave, PASSWORD_DEFAULT);
$u = $conn->prepare("UPDATE usuarios SET clave = ?, intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id_usuario = ?");
$u->bind_param("si", $hash, $fila['id_usuario']);
$u->execute();
echo "Contraseña actualizada para '$usuario'.\n";
