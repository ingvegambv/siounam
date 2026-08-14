<?php
// models/Grupo.php

require_once __DIR__ . '/../includes/connection.php';

class Grupo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los grupos
     */
    public function getAll()
    {
        $query = "SELECT g.*, c.nombre_carrera 
                  FROM grupo_unam g
                  INNER JOIN carrera_unam c ON g.id_carrera = c.id_carrera
                  ORDER BY c.nombre_carrera, g.nombre_grupo";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene un grupo por ID
     */
    public function getById($id)
    {
        $query = "SELECT g.*, c.nombre_carrera 
                  FROM grupo_unam g
                  INNER JOIN carrera_unam c ON g.id_carrera = c.id_carrera
                  WHERE g.id_grupounam = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene grupos por carrera
     */
    public function getByCarrera($idCarrera)
    {
        $query = "SELECT g.*, c.nombre_carrera 
                  FROM grupo_unam g
                  INNER JOIN carrera_unam c ON g.id_carrera = c.id_carrera
                  WHERE g.id_carrera = ?
                  ORDER BY g.nombre_grupo";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea un nuevo grupo
     */
    public function create($data)
    {
        $query = "INSERT INTO grupo_unam (nombre_grupo, id_carrera) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $data['nombre_grupo'], $data['id_carrera']);
        return $stmt->execute();
    }

    /**
     * Actualiza un grupo
     */
    public function update($id, $data)
    {
        $query = "UPDATE grupo_unam SET nombre_grupo = ?, id_carrera = ? WHERE id_grupounam = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sii", $data['nombre_grupo'], $data['id_carrera'], $id);
        return $stmt->execute();
    }

    /**
     * Elimina un grupo
     */
    public function delete($id)
    {
        $query = "DELETE FROM grupo_unam WHERE id_grupounam = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Cuenta grupos por carrera
     */
    public function countByCarrera($idCarrera)
    {
        $query = "SELECT COUNT(*) as total FROM grupo_unam WHERE id_carrera = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? (int)$result['total'] : 0;
    }
}
?>