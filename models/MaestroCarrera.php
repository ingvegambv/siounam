<?php
// models/MaestroCarrera.php

require_once __DIR__ . '/../includes/connection.php';

class MaestroCarrera
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las asignaciones maestro-carrera
     */
    public function getAll() {
        $query = "SELECT mc.*, u.nombre, u.apellido_paterno, u.apellido_materno, 
                         u.usuario, c.nombre_carrera
                  FROM maestro_carrera mc
                  INNER JOIN usuarios_unam u ON mc.id_usuario = u.id_usuario
                  INNER JOIN carrera_unam c ON mc.id_carrera = c.id_carrera
                  WHERE u.activo = 1
                  ORDER BY c.nombre_carrera, u.apellido_paterno";
        
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene maestros por carrera
     */
    public function getByCarrera($idCarrera) {
        $query = "SELECT mc.*, u.id_usuario, u.nombre, u.apellido_paterno, 
                         u.apellido_materno, u.usuario, u.correo
                  FROM maestro_carrera mc
                  INNER JOIN usuarios_unam u ON mc.id_usuario = u.id_usuario
                  WHERE mc.id_carrera = ? AND u.activo = 1
                  ORDER BY u.apellido_paterno, u.nombre";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene carreras de un maestro
     */
    public function getCarrerasByMaestro($idUsuario) {
        $query = "SELECT mc.*, c.nombre_carrera
                  FROM maestro_carrera mc
                  INNER JOIN carrera_unam c ON mc.id_carrera = c.id_carrera
                  WHERE mc.id_usuario = ?";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Asigna un maestro a una carrera
     */
    public function assign($idUsuario, $idCarrera) {
        // Verificar duplicado
        if ($this->exists($idUsuario, $idCarrera)) {
            return false;
        }

        $query = "INSERT INTO maestro_carrera (id_usuario, id_carrera) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $idUsuario, $idCarrera);
        return $stmt->execute();
    }

    /**
     * Verifica si ya existe la asignación
     */
    public function exists($idUsuario, $idCarrera) {
        $query = "SELECT COUNT(*) FROM maestro_carrera 
                  WHERE id_usuario = ? AND id_carrera = ?";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $idUsuario, $idCarrera);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        return $result[0] > 0;
    }

    /**
     * Elimina asignación por ID
     */
    public function delete($idMaestroCarrera) {
        $query = "DELETE FROM maestro_carrera WHERE id_maestro_carrera = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $idMaestroCarrera);
        return $stmt->execute();
    }

    /**
     * Elimina asignación por usuario y carrera
     */
    public function deleteByUsuarioCarrera($idUsuario, $idCarrera) {
        $query = "DELETE FROM maestro_carrera WHERE id_usuario = ? AND id_carrera = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("ii", $idUsuario, $idCarrera);
        return $stmt->execute();
    }

    /**
     * Elimina todas las asignaciones de un maestro
     */
    public function deleteAllByMaestro($idUsuario) {
        $query = "DELETE FROM maestro_carrera WHERE id_usuario = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $idUsuario);
        return $stmt->execute();
    }

    /**
     * Obtiene maestros disponibles (no asignados a una carrera específica)
     */
    public function getAvailableMaestros($idCarrera) {
        $query = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, 
                         u.apellido_materno, u.usuario
                  FROM usuarios_unam u
                  WHERE u.id_rol = 3 
                    AND u.activo = 1
                    AND u.id_usuario NOT IN (
                        SELECT id_usuario 
                        FROM maestro_carrera 
                        WHERE id_carrera = ?
                    )
                  ORDER BY u.apellido_paterno, u.nombre";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene todos los maestros
     */
    public function getAllMaestros() {
        $query = "SELECT u.id_usuario, u.nombre, u.apellido_paterno, 
                         u.apellido_materno, u.usuario, u.correo
                  FROM usuarios_unam u
                  WHERE u.id_rol = 3 AND u.activo = 1
                  ORDER BY u.apellido_paterno, u.nombre";
        
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>