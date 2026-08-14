<?php
// models/Materia.php

require_once __DIR__ . '/../includes/connection.php';

class Materia
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todas las materias
     */
    public function getAll()
    {
        $query = "SELECT m.*, c.nombre_carrera, s.nombre_semestre 
                  FROM materias m
                  INNER JOIN carrera_unam c ON m.id_carrera = c.id_carrera
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  ORDER BY c.nombre_carrera, m.id_semestre, m.nombre_materia";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene una materia por ID
     */
    public function getById($id)
    {
        $query = "SELECT m.*, c.nombre_carrera, s.nombre_semestre 
                  FROM materias m
                  INNER JOIN carrera_unam c ON m.id_carrera = c.id_carrera
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  WHERE m.id_materia = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene materias por carrera
     */
    public function getByCarrera($idCarrera)
    {
        $query = "SELECT m.*, c.nombre_carrera, s.nombre_semestre 
                  FROM materias m
                  INNER JOIN carrera_unam c ON m.id_carrera = c.id_carrera
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  WHERE m.id_carrera = ?
                  ORDER BY m.id_semestre, m.nombre_materia";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene materias por carrera y semestre
     */
    public function getByCarreraSemestre($idCarrera, $idSemestre)
    {
        $query = "SELECT m.*, c.nombre_carrera, s.nombre_semestre 
                  FROM materias m
                  INNER JOIN carrera_unam c ON m.id_carrera = c.id_carrera
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  WHERE m.id_carrera = ? AND m.id_semestre = ?
                  ORDER BY m.nombre_materia";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $idCarrera, $idSemestre);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene semestres disponibles por carrera
     */
    public function getSemestresByCarrera($idCarrera)
    {
        $query = "SELECT DISTINCT s.id_semestre, s.nombre_semestre 
                  FROM materias m
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  WHERE m.id_carrera = ?
                  ORDER BY s.id_semestre";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Si no hay materias, devolver todos los semestres
        if (empty($result)) {
            $query = "SELECT * FROM semestre ORDER BY id_semestre";
            $result = $this->db->query($query);
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
        
        return $result;
    }

    /**
     * Crea una nueva materia
     */
    public function create($data)
    {
        $query = "INSERT INTO materias (nombre_materia, id_carrera, id_semestre) VALUES (?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("sii", $data['nombre_materia'], $data['id_carrera'], $data['id_semestre']);
        return $stmt->execute();
    }

    /**
     * Actualiza una materia
     */
    public function update($id, $data)
    {
        $query = "UPDATE materias SET nombre_materia = ?, id_carrera = ?, id_semestre = ? WHERE id_materia = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("siii", $data['nombre_materia'], $data['id_carrera'], $data['id_semestre'], $id);
        return $stmt->execute();
    }

    /**
     * Elimina una materia
     */
    public function delete($id)
    {
        $query = "DELETE FROM materias WHERE id_materia = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
?>