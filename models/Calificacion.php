<?php
// models/Calificacion.php

require_once __DIR__ . '/../includes/connection.php';

class Calificacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene calificación por alumno y materia
     */
    public function getByAlumnoMateria($idAlumno, $idMateria)
    {
        $query = "SELECT c.* 
                  FROM calificaciones_unam c
                  INNER JOIN asignacion_maestros a ON c.id_asignacion = a.id_asignacion
                  WHERE c.id_alumno = ? AND a.id_materia = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $idAlumno, $idMateria);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene calificaciones por alumno
     */
    public function getByAlumno($idAlumno)
    {
        $query = "SELECT c.*, m.nombre_materia, g.nombre_grupo
                  FROM calificaciones_unam c
                  INNER JOIN asignacion_maestros a ON c.id_asignacion = a.id_asignacion
                  INNER JOIN materias m ON a.id_materia = m.id_materia
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  WHERE c.id_alumno = ?
                  ORDER BY m.nombre_materia";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea o actualiza una calificación
     */
    public function save($data)
    {
        // Verificar si existe
        $query = "SELECT id_calificacion FROM calificaciones_unam 
                  WHERE id_alumno = ? AND id_asignacion = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ii", $data['id_alumno'], $data['id_asignacion']);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();
        
        if ($exists) {
            // Actualizar
            return $this->update($exists['id_calificacion'], $data);
        } else {
            // Crear
            return $this->create($data);
        }
    }

    /**
     * Crea una nueva calificación
     */
    public function create($data)
    {
        $query = "INSERT INTO calificaciones_unam 
                  (id_alumno, id_asignacion, parcial1, faltas1, parcial2, faltas2, 
                   parcial3, faltas3, parcial4, faltas4, promedioparciales, 
                   vuelta1, vuelta2, promediofinal) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "iididididddddd",
            $data['id_alumno'],
            $data['id_asignacion'],
            $data['parcial1'] ?? null,
            $data['faltas1'] ?? 0,
            $data['parcial2'] ?? null,
            $data['faltas2'] ?? 0,
            $data['parcial3'] ?? null,
            $data['faltas3'] ?? 0,
            $data['parcial4'] ?? null,
            $data['faltas4'] ?? 0,
            $data['promedioparciales'] ?? null,
            $data['vuelta1'] ?? null,
            $data['vuelta2'] ?? null,
            $data['promediofinal'] ?? null
        );
        return $stmt->execute();
    }

    /**
     * Actualiza una calificación existente
     */
    public function update($id, $data)
    {
        $query = "UPDATE calificaciones_unam SET 
                  parcial1 = ?, faltas1 = ?, parcial2 = ?, faltas2 = ?, 
                  parcial3 = ?, faltas3 = ?, parcial4 = ?, faltas4 = ?, 
                  promedioparciales = ?, vuelta1 = ?, vuelta2 = ?, promediofinal = ?
                  WHERE id_calificacion = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param(
            "diddidddddddi",
            $data['parcial1'] ?? null,
            $data['faltas1'] ?? 0,
            $data['parcial2'] ?? null,
            $data['faltas2'] ?? 0,
            $data['parcial3'] ?? null,
            $data['faltas3'] ?? 0,
            $data['parcial4'] ?? null,
            $data['faltas4'] ?? 0,
            $data['promedioparciales'] ?? null,
            $data['vuelta1'] ?? null,
            $data['vuelta2'] ?? null,
            $data['promediofinal'] ?? null,
            $id
        );
        return $stmt->execute();
    }
}
?>