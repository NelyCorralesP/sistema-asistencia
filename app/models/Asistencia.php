<?php
class Asistencia
{
    public static function delDia($conn, $id_personal, $fecha)
    {
        $q = $conn->prepare("SELECT id_asistencia, hora_entrada, hora_salida, estado FROM asistencia WHERE id_personal = ? AND fecha = ?");
        $q->bind_param("is", $id_personal, $fecha);
        $q->execute();
        return $q->get_result()->fetch_assoc();
    }

    public static function registrarEntrada($conn, $id_personal, $fecha, $hora, $estado)
    {
        $q = $conn->prepare("INSERT INTO asistencia (id_personal, fecha, hora_entrada, estado) VALUES (?, ?, ?, ?)");
        $q->bind_param("isss", $id_personal, $fecha, $hora, $estado);
        return $q->execute();
    }

    public static function registrarSalida($conn, $id_asistencia, $hora)
    {
        $q = $conn->prepare("UPDATE asistencia SET hora_salida = ? WHERE id_asistencia = ?");
        $q->bind_param("si", $hora, $id_asistencia);
        return $q->execute();
    }

    // Inasistencia: registro del día sin horas, con estado "Falta"
    public static function registrarFalta($conn, $id_personal, $fecha)
    {
        $q = $conn->prepare("INSERT INTO asistencia (id_personal, fecha, estado) VALUES (?, ?, 'Falta')");
        $q->bind_param("is", $id_personal, $fecha);
        return $q->execute();
    }

    public static function anularFalta($conn, $id_asistencia)
    {
        $q = $conn->prepare("DELETE FROM asistencia WHERE id_asistencia = ? AND estado = 'Falta'");
        $q->bind_param("i", $id_asistencia);
        return $q->execute();
    }

    public static function listaDelDia($conn, $fecha)
    {
        $q = $conn->prepare("SELECT p.id_personal, p.nombres, p.dni, p.cargo, p.hora_entrada AS horario, a.hora_entrada, a.hora_salida, a.estado
                             FROM personal p
                             LEFT JOIN asistencia a ON a.id_personal = p.id_personal AND a.fecha = ?
                             ORDER BY p.nombres");
        $q->bind_param("s", $fecha);
        $q->execute();
        return $q->get_result();
    }

    // Registros filtrados por rango de fechas, trabajador (0 = todos) y estado ('' = todos)
    public static function consultar($conn, $desde, $hasta, $id_personal = 0, $estado = '')
    {
        $sql = "SELECT a.fecha, p.id_personal, p.nombres, p.dni, p.cargo, a.hora_entrada, a.hora_salida, a.estado,
                       TIME_FORMAT(TIMEDIFF(a.hora_salida, a.hora_entrada), '%H:%i') AS horas
                FROM asistencia a
                JOIN personal p ON p.id_personal = a.id_personal
                WHERE a.fecha BETWEEN ? AND ?";
        $tipos = "ss";
        $vals = [$desde, $hasta];
        if ($id_personal > 0) { $sql .= " AND a.id_personal = ?"; $tipos .= "i"; $vals[] = $id_personal; }
        if ($estado != '')    { $sql .= " AND a.estado = ?";      $tipos .= "s"; $vals[] = $estado; }
        $sql .= " ORDER BY a.fecha DESC, p.nombres";

        $q = $conn->prepare($sql);
        $q->bind_param($tipos, ...$vals);
        $q->execute();
        return $q->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // Resumen por trabajador en el rango de fechas
    public static function resumenPorPersonal($conn, $desde, $hasta, $id_personal = 0)
    {
        $sql = "SELECT p.nombres, p.cargo,
                       SUM(a.estado = 'Puntual') AS puntuales,
                       SUM(a.estado = 'Tardanza') AS tardanzas,
                       SUM(a.estado = 'Falta') AS faltas,
                       COUNT(*) AS total
                FROM asistencia a
                JOIN personal p ON p.id_personal = a.id_personal
                WHERE a.fecha BETWEEN ? AND ?";
        $tipos = "ss";
        $vals = [$desde, $hasta];
        if ($id_personal > 0) { $sql .= " AND a.id_personal = ?"; $tipos .= "i"; $vals[] = $id_personal; }
        $sql .= " GROUP BY p.id_personal, p.nombres, p.cargo ORDER BY p.nombres";

        $q = $conn->prepare($sql);
        $q->bind_param($tipos, ...$vals);
        $q->execute();
        return $q->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
