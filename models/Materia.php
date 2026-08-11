<?php
// models/Materia.php

require_once __DIR__ . '/../includes/connection.php';

class Materia
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT m.*, c.nombre_carrera, s.nombre_semestre
            FROM materias m
            INNER JOIN carrera_unam c ON m.id_carrera = c.id_carrera
            INNER JOIN semestre s ON m.id_semestre = s.id_semestre
            ORDER BY m.nombre_materia
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM materias WHERE id_materia = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create(array $data): bool
    {
        if (empty($data['nombre_materia']) || empty($data['id_carrera']) || empty($data['id_semestre'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO materias (nombre_materia, id_carrera, id_semestre) VALUES (?, ?, ?)"
        );
        if (!$stmt) {
            return false;
        }
        
        $nombre = $data['nombre_materia'];
        $idCarrera = (int)$data['id_carrera'];
        $idSemestre = (int)$data['id_semestre'];
        
        $stmt->bind_param("sii", $nombre, $idCarrera, $idSemestre);
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        if (empty($data['nombre_materia']) || empty($data['id_carrera']) || empty($data['id_semestre'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "UPDATE materias SET nombre_materia = ?, id_carrera = ?, id_semestre = ? WHERE id_materia = ?"
        );
        if (!$stmt) {
            return false;
        }
        
        $nombre = $data['nombre_materia'];
        $idCarrera = (int)$data['id_carrera'];
        $idSemestre = (int)$data['id_semestre'];
        
        $stmt->bind_param("siii", $nombre, $idCarrera, $idSemestre, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM materias WHERE id_materia = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}