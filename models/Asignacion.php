<?php
// models/Asignacion.php

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
            SELECT 
                a.id_asignacion,
                a.id_grupounam,
                a.id_materia,
                a.id_usuario,
                g.nombre_grupo,
                m.nombre_materia,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_maestro,
                c.nombre_carrera,
                s.nombre_semestre
            FROM asignacion_maestros a
            INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
            INNER JOIN materias m ON a.id_materia = m.id_materia
            INNER JOIN usuarios_unam u ON a.id_usuario = u.id_usuario
            INNER JOIN carrera_unam c ON g.id_carrera = c.id_carrera
            INNER JOIN semestre s ON m.id_semestre = s.id_semestre
            ORDER BY g.nombre_grupo, m.nombre_materia
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM asignacion_maestros WHERE id_asignacion = ?");
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create(array $data): bool
    {
        if (empty($data['id_grupounam']) || empty($data['id_materia']) || empty($data['id_usuario'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO asignacion_maestros (id_grupounam, id_materia, id_usuario) VALUES (?, ?, ?)"
        );
        if (!$stmt) {
            return false;
        }
        
        $idGrupo = (int)$data['id_grupounam'];
        $idMateria = (int)$data['id_materia'];
        $idUsuario = (int)$data['id_usuario'];
        
        $stmt->bind_param("iii", $idGrupo, $idMateria, $idUsuario);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM asignacion_maestros WHERE id_asignacion = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function deleteAll(): bool
    {
        $stmt = $this->db->prepare("DELETE FROM asignacion_maestros");
        if (!$stmt) {
            return false;
        }
        return $stmt->execute();
    }

    public function verificarMaestro(int $idUsuario): bool
    {
        $stmt = $this->db->prepare("SELECT id_usuario FROM usuarios_unam WHERE id_usuario = ? AND id_rol = 3 AND activo = 1");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function verificarGrupo(int $idGrupo): bool
    {
        $stmt = $this->db->prepare("SELECT id_grupounam FROM grupo_unam WHERE id_grupounam = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function verificarMateria(int $idMateria): bool
    {
        $stmt = $this->db->prepare("SELECT id_materia FROM materias WHERE id_materia = ?");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $idMateria);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function verificarDuplicado(int $idGrupo, int $idMateria, int $idUsuario): bool
    {
        $stmt = $this->db->prepare(
            "SELECT id_asignacion FROM asignacion_maestros 
             WHERE id_grupounam = ? AND id_materia = ? AND id_usuario = ?"
        );
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("iii", $idGrupo, $idMateria, $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}