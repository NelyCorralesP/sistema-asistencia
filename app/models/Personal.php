<?php
class Personal
{
    public static function buscarPorDni($conn, $dni, $excluirId = 0)
    {
        $q = $conn->prepare("SELECT id_personal, nombres, dni, cargo, hora_entrada FROM personal WHERE dni = ? AND id_personal <> ?");
        $q->bind_param("si", $dni, $excluirId);
        $q->execute();
        return $q->get_result()->fetch_assoc();
    }

    public static function buscarPorId($conn, $id)
    {
        $q = $conn->prepare("SELECT id_personal, nombres, dni, cargo, hora_entrada FROM personal WHERE id_personal = ?");
        $q->bind_param("i", $id);
        $q->execute();
        return $q->get_result()->fetch_assoc();
    }

    public static function crear($conn, $nombres, $dni, $cargo, $hora_entrada)
    {
        $stmt = $conn->prepare("INSERT INTO personal (nombres, dni, cargo, hora_entrada) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombres, $dni, $cargo, $hora_entrada);
        return $stmt->execute();
    }

    public static function actualizar($conn, $id, $nombres, $dni, $cargo, $hora_entrada)
    {
        $stmt = $conn->prepare("UPDATE personal SET nombres = ?, dni = ?, cargo = ?, hora_entrada = ? WHERE id_personal = ?");
        $stmt->bind_param("ssssi", $nombres, $dni, $cargo, $hora_entrada, $id);
        return $stmt->execute();
    }

    public static function listar($conn)
    {
        return $conn->query("SELECT id_personal, nombres, dni, cargo, hora_entrada FROM personal ORDER BY nombres");
    }

    public static function horaEntrada($conn, $id_personal)
    {
        $q = $conn->prepare("SELECT hora_entrada FROM personal WHERE id_personal = ?");
        $q->bind_param("i", $id_personal);
        $q->execute();
        return $q->get_result()->fetch_assoc();
    }
}
