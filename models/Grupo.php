<?php

require_once __DIR__ . '/../includes/connection.php';

class Grupo
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll($idCarrera = null): array
    {
        $sql = "
            SELECT g.*, c.nombre_carrera, 
                   (SELECT COUNT(*) FROM alumnos_unam a WHERE a.id_grupounam = g.id_grupounam) as total_alumnos
            FROM grupo_unam g
            INNER JOIN carrera_unam c ON g.id_carrera = c.id_carrera
        ";
        
        if ($idCarrera) {
            $sql .= " WHERE g.id_carrera = $idCarrera";
        }
        
        $sql .= " ORDER BY g.nombre_grupo";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO grupo_unam (id_carrera, nombre_grupo) VALUES (?, ?)"
        );
        $stmt->bind_param("is", $data['id_carrera'], $data['nombre_grupo']);
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE grupo_unam SET id_carrera = ?, nombre_grupo = ? WHERE id_grupounam = ?"
        );
        $stmt->bind_param("isi", $data['id_carrera'], $data['nombre_grupo'], $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM grupo_unam WHERE id_grupounam = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}