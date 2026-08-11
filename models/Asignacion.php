<?php

require_once __DIR__ . '/../includes/connection.php';

class Asignacion
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT a.*, 
                   g.nombre_grupo, 
                   m.nombre_materia,
                   CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_maestro,
                   c.nombre_carrera
            FROM asignacion_maestros a
            INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
            INNER JOIN materias m ON a.id_materia = m.id_materia
            INNER JOIN usuarios_unam u ON a.id_usuario = u.id_usuario
            INNER JOIN carrera_unam c ON g.id_carrera = c.id_carrera
            ORDER BY g.nombre_grupo, m.nombre_materia
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO asignacion_maestros (id_grupounam, id_materia, id_usuario) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("iii", $data['id_grupounam'], $data['id_materia'], $data['id_usuario']);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM asignacion_maestros WHERE id_asignacion = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}