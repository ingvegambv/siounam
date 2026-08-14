<?php
// models/Asignacion.php

require_once __DIR__ . '/../includes/connection.php';

class Asignacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las asignaciones
     */
    public function getAll()
    {
        $query = "SELECT a.*, 
                         g.nombre_grupo, 
                         m.nombre_materia,
                         CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_maestro
                  FROM asignacion_maestros a
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  INNER JOIN materias m ON a.id_materia = m.id_materia
                  INNER JOIN usuarios_unam u ON a.id_usuario = u.id_usuario
                  ORDER BY g.nombre_grupo, m.nombre_materia";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene una asignación por ID
     */
    public function getById($id)
    {
        $query = "SELECT a.*, 
                         g.nombre_grupo, 
                         m.nombre_materia,
                         CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_maestro
                  FROM asignacion_maestros a
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  INNER JOIN materias m ON a.id_materia = m.id_materia
                  INNER JOIN usuarios_unam u ON a.id_usuario = u.id_usuario
                  WHERE a.id_asignacion = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene asignaciones por carrera
     */
    public function getByCarrera($idCarrera)
    {
        $query = "SELECT a.*, 
                         g.nombre_grupo, 
                         m.nombre_materia,
                         CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_maestro
                  FROM asignacion_maestros a
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  INNER JOIN materias m ON a.id_materia = m.id_materia
                  INNER JOIN usuarios_unam u ON a.id_usuario = u.id_usuario
                  WHERE g.id_carrera = ?
                  ORDER BY g.nombre_grupo, m.nombre_materia";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene asignación por materia y grupo
     */
    public function getByMateriaGrupo($idMateria, $idGrupo)
    {
        $query = "SELECT a.*, 
                         CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_maestro
                  FROM asignacion_maestros a
                  INNER JOIN usuarios_unam u ON a.id_usuario = u.id_usuario
                  WHERE a.id_materia = ? AND a.id_grupounam = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $idMateria, $idGrupo);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Crea una nueva asignación
     */
    public function create($data)
    {
        // Verificar duplicado
        if ($this->verificarDuplicado($data['id_grupounam'], $data['id_materia'], $data['id_usuario'])) {
            return false;
        }
        
        $query = "INSERT INTO asignacion_maestros (id_grupounam, id_materia, id_usuario) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $data['id_grupounam'], $data['id_materia'], $data['id_usuario']);
        return $stmt->execute();
    }

    /**
     * Elimina una asignación
     */
    public function delete($id)
    {
        $query = "DELETE FROM asignacion_maestros WHERE id_asignacion = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Elimina todas las asignaciones
     */
    public function deleteAll()
    {
        $query = "DELETE FROM asignacion_maestros";
        return $this->db->query($query);
    }

    /**
     * Verifica si un usuario es maestro
     */
    public function verificarMaestro($idUsuario)
    {
        $query = "SELECT id_rol FROM usuarios_unam WHERE id_usuario = ? AND activo = 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result && $result['id_rol'] == 3;
    }

    /**
     * Verifica si un grupo existe
     */
    public function verificarGrupo($idGrupo)
    {
        $query = "SELECT id_grupounam FROM grupo_unam WHERE id_grupounam = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Verifica si una materia existe
     */
    public function verificarMateria($idMateria)
    {
        $query = "SELECT id_materia FROM materias WHERE id_materia = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idMateria);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    /**
     * Verifica si una asignación ya existe
     */
    public function verificarDuplicado($idGrupo, $idMateria, $idUsuario)
    {
        $query = "SELECT id_asignacion FROM asignacion_maestros 
                  WHERE id_grupounam = ? AND id_materia = ? AND id_usuario = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("iii", $idGrupo, $idMateria, $idUsuario);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
?>