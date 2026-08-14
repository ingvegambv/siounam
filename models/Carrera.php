<?php
// models/Carrera.php

require_once __DIR__ . '/../includes/connection.php';

class Carrera
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las carreras
     */
    public function getAll()
    {
        $query = "SELECT * FROM carrera_unam ORDER BY nombre_carrera";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene una carrera por ID
     */
    public function getById($id)
    {
        $query = "SELECT * FROM carrera_unam WHERE id_carrera = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Obtiene carreras por nombre (búsqueda)
     */
    public function searchByName($nombre)
    {
        $query = "SELECT * FROM carrera_unam WHERE nombre_carrera LIKE ? ORDER BY nombre_carrera";
        $stmt = $this->db->prepare($query);
        $searchTerm = "%$nombre%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea una nueva carrera
     */
    public function create($nombre)
    {
        $query = "INSERT INTO carrera_unam (nombre_carrera) VALUES (?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $nombre);
        return $stmt->execute();
    }

    /**
     * Actualiza una carrera
     */
    public function update($id, $nombre)
    {
        $query = "UPDATE carrera_unam SET nombre_carrera = ? WHERE id_carrera = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("si", $nombre, $id);
        return $stmt->execute();
    }

    /**
     * Elimina una carrera
     */
    public function delete($id)
    {
        $query = "DELETE FROM carrera_unam WHERE id_carrera = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Obtiene el total de carreras
     */
    public function count()
    {
        $query = "SELECT COUNT(*) as total FROM carrera_unam";
        $result = $this->db->query($query);
        $row = $result->fetch_assoc();
        return $row ? (int)$row['total'] : 0;
    }

    /**
     * Obtiene carreras con conteo de alumnos
     */
    public function getWithAlumnosCount()
    {
        $query = "SELECT c.*, COUNT(a.id_alumno) as total_alumnos 
                  FROM carrera_unam c
                  LEFT JOIN alumnos_unam a ON c.id_carrera = a.id_carrera
                  GROUP BY c.id_carrera
                  ORDER BY c.nombre_carrera";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene carreras con conteo de materias
     */
    public function getWithMateriasCount()
    {
        $query = "SELECT c.*, COUNT(m.id_materia) as total_materias 
                  FROM carrera_unam c
                  LEFT JOIN materias m ON c.id_carrera = m.id_carrera
                  GROUP BY c.id_carrera
                  ORDER BY c.nombre_carrera";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>