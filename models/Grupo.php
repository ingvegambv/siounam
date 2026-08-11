<?php
// models/Grupo.php

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
            $sql .= " WHERE g.id_carrera = " . (int)$idCarrera;
        }
        
        $sql .= " ORDER BY g.nombre_grupo";
        
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM grupo_unam WHERE id_grupounam = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create(array $data): bool
    {
        if (empty($data['nombre_grupo']) || empty($data['id_carrera'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO grupo_unam (id_carrera, nombre_grupo) VALUES (?, ?)"
        );
        if (!$stmt) {
            return false;
        }
        
        $idCarrera = (int)$data['id_carrera'];
        $nombre = $data['nombre_grupo'];
        
        $stmt->bind_param("is", $idCarrera, $nombre);
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data['nombre_grupo']) || empty($data['id_carrera'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE grupo_unam SET id_carrera = ?, nombre_grupo = ? WHERE id_grupounam = ?"
        );
        if (!$stmt) {
            return false;
        }
        
        $idCarrera = (int)$data['id_carrera'];
        $nombre = $data['nombre_grupo'];
        
        $stmt->bind_param("isi", $idCarrera, $nombre, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM grupo_unam WHERE id_grupounam = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}