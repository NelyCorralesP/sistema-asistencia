<?php
date_default_timezone_set('America/Lima');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "bd_infortec";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>