<?php

require_once __DIR__ . '/../includes/connection.php';

class Alumno
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT a.*, c.nombre_carrera, g.nombre_grupo
            FROM alumnos_unam a
            INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
            INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
            ORDER BY a.nombre
        ";
        $result = $this->db->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare("SELECT * FROM alumnos_unam WHERE id_alumno = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare(
            "INSERT INTO alumnos_unam (id_carrera, id_grupounam, nombre, apellido_paterno, apellido_materno) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("iisss", $data['id_carrera'], $data['id_grupounam'], 
                         $data['nombre'], $data['apellido_paterno'], $data['apellido_materno']);
        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE alumnos_unam SET id_carrera = ?, id_grupounam = ?, nombre = ?, 
             apellido_paterno = ?, apellido_materno = ? WHERE id_alumno = ?"
        );
        $stmt->bind_param("iisssi", $data['id_carrera'], $data['id_grupounam'], 
                         $data['nombre'], $data['apellido_paterno'], $data['apellido_materno'], $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM alumnos_unam WHERE id_alumno = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function migrar(int $grupoOrigen, int $grupoDestino): array
    {
        $this->db->begin_transaction();
        
        try {
            // Actualizar alumnos
            $stmt = $this->db->prepare(
                "UPDATE alumnos_unam SET id_grupounam = ? WHERE id_grupounam = ?"
            );
            $stmt->bind_param("ii", $grupoDestino, $grupoOrigen);
            $stmt->execute();
            $migrados = $stmt->affected_rows;
            
            $this->db->commit();
            
            return [
                'success' => true,
                'migrados' => $migrados
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}