<?php
// models/CalificacionParcial.php

require_once __DIR__ . '/../includes/connection.php';

class CalificacionParcial
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene calificaciones de un alumno para una asignación y parcial
     */
    public function getByAlumnoAsignacionParcial($idAlumno, $idAsignacion, $idParcial)
    {
        $query = "SELECT cp.*, ae.nombre as nombre_aspecto, ae.porcentaje,
                         ce.id_configuracion
                  FROM calificaciones_parciales cp
                  INNER JOIN aspectos_evaluacion ae ON cp.id_aspecto = ae.id_aspecto
                  INNER JOIN configuracion_evaluacion ce ON ae.id_configuracion = ce.id_configuracion
                  INNER JOIN calificaciones_unam c ON cp.id_calificacion = c.id_calificacion
                  WHERE c.id_alumno = ? 
                    AND c.id_asignacion = ? 
                    AND ce.id_parcial = ?
                  ORDER BY ae.orden";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) return [];
        $stmt->bind_param("iii", $idAlumno, $idAsignacion, $idParcial);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene todas las calificaciones de una asignación y parcial
     */
    public function getByAsignacionParcial($idAsignacion, $idParcial)
    {
        $query = "SELECT 
                    a.id_alumno, 
                    a.nombre, 
                    a.apellido_paterno, 
                    a.apellido_materno,
                    a.matricula,
                    c.id_calificacion,
                    ce.id_configuracion,
                    ae.id_aspecto,
                    ae.nombre as nombre_aspecto,
                    ae.porcentaje,
                    cp.calificacion,
                    fp.cantidad_faltas,
                    ce.limite_faltas
                  FROM asignacion_maestros am
                  INNER JOIN configuracion_evaluacion ce ON am.id_asignacion = ce.id_asignacion
                  INNER JOIN aspectos_evaluacion ae ON ce.id_configuracion = ae.id_configuracion
                  INNER JOIN calificaciones_unam c ON c.id_asignacion = am.id_asignacion
                  INNER JOIN alumnos_unam a ON c.id_alumno = a.id_alumno
                  LEFT JOIN calificaciones_parciales cp ON cp.id_calificacion = c.id_calificacion AND cp.id_aspecto = ae.id_aspecto
                  LEFT JOIN faltas_parciales fp ON fp.id_calificacion = c.id_calificacion AND fp.id_parcial = ?
                  WHERE am.id_asignacion = ? AND ce.id_parcial = ?
                  ORDER BY a.apellido_paterno, a.nombre";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) return [];
        $stmt->bind_param("iii", $idParcial, $idAsignacion, $idParcial);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Guardar calificación de un aspecto
     */
    public function guardarCalificacion($idCalificacion, $idAspecto, $calificacion)
    {
        // Verificar si existe
        $query = "SELECT id_calificacion_parcial FROM calificaciones_parciales 
                  WHERE id_calificacion = ? AND id_aspecto = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $idCalificacion, $idAspecto);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();

        if ($exists) {
            $query = "UPDATE calificaciones_parciales SET calificacion = ? 
                      WHERE id_calificacion = ? AND id_aspecto = ?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) return false;
            $stmt->bind_param("dii", $calificacion, $idCalificacion, $idAspecto);
        } else {
            $query = "INSERT INTO calificaciones_parciales (id_calificacion, id_aspecto, calificacion) 
                      VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if (!$stmt) return false;
            $stmt->bind_param("iid", $idCalificacion, $idAspecto, $calificacion);
        }
        return $stmt->execute();
    }

    /**
     * Guardar faltas de un alumno en un parcial
     */
    public function guardarFaltas($idCalificacion, $idParcial, $cantidad)
    {
        // Verificar si existe
        $query = "SELECT id_falta FROM faltas_parciales 
                  WHERE id_calificacion = ? AND id_parcial = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $idCalificacion, $idParcial);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();

        if ($exists) {
            $query = "UPDATE faltas_parciales SET cantidad_faltas = ? 
                      WHERE id_calificacion = ? AND id_parcial = ?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) return false;
            $stmt->bind_param("iii", $cantidad, $idCalificacion, $idParcial);
        } else {
            $query = "INSERT INTO faltas_parciales (id_calificacion, id_parcial, cantidad_faltas) 
                      VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if (!$stmt) return false;
            $stmt->bind_param("iii", $idCalificacion, $idParcial, $cantidad);
        }
        return $stmt->execute();
    }

    /**
     * Calcular promedio final del alumno para un parcial
     */
    public function calcularPromedio($idCalificacion, $idConfiguracion)
    {
        // Obtener todos los aspectos y calificaciones
        $query = "SELECT ae.porcentaje, cp.calificacion
                  FROM aspectos_evaluacion ae
                  LEFT JOIN calificaciones_parciales cp ON cp.id_aspecto = ae.id_aspecto AND cp.id_calificacion = ?
                  WHERE ae.id_configuracion = ?";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) return null;
        $stmt->bind_param("ii", $idCalificacion, $idConfiguracion);
        $stmt->execute();
        $resultados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        $promedio = 0;
        $ponderado = 0;

        foreach ($resultados as $row) {
            if ($row['calificacion'] !== null) {
                $promedio += $row['calificacion'] * ($row['porcentaje'] / 100);
                $ponderado += $row['porcentaje'];
            }
        }

        // Si no tiene calificaciones en todos los aspectos, retornar null
        if ($ponderado < 100) {
            return null;
        }

        return round($promedio, 2);
    }

    /**
     * Verificar si el parcial está bloqueado
     */
    public function isParcialBloqueado($idAsignacion, $idParcial)
    {
        $query = "SELECT bloqueado FROM configuracion_evaluacion 
                  WHERE id_asignacion = ? AND id_parcial = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("ii", $idAsignacion, $idParcial);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        return $result ? (bool)$result['bloqueado'] : false;
    }

    /**
     * Obtener todas las calificaciones de un alumno en todas las materias
     */
    public function getBoletaAlumno($idAlumno, $idMaestro)
    {
        $query = "SELECT 
                    am.id_asignacion,
                    m.nombre_materia,
                    g.nombre_grupo,
                    c.nombre_carrera,
                    ce.id_parcial,
                    ce.numero_parciales,
                    ce.limite_faltas,
                    fp.cantidad_faltas,
                    (SELECT ROUND(AVG(cp.calificacion * ae.porcentaje / 100), 2)
                     FROM calificaciones_parciales cp
                     INNER JOIN aspectos_evaluacion ae ON cp.id_aspecto = ae.id_aspecto
                     WHERE cp.id_calificacion = cal.id_calificacion
                     AND ae.id_configuracion = ce.id_configuracion) as promedio_final
                  FROM asignacion_maestros am
                  INNER JOIN materias m ON am.id_materia = m.id_materia
                  INNER JOIN grupo_unam g ON am.id_grupounam = g.id_grupounam
                  INNER JOIN carrera_unam c ON g.id_carrera = c.id_carrera
                  INNER JOIN calificaciones_unam cal ON cal.id_asignacion = am.id_asignacion
                  LEFT JOIN configuracion_evaluacion ce ON ce.id_asignacion = am.id_asignacion
                  LEFT JOIN faltas_parciales fp ON fp.id_calificacion = cal.id_calificacion AND fp.id_parcial = ce.id_parcial
                  WHERE cal.id_alumno = ? AND am.id_usuario = ?
                  GROUP BY am.id_asignacion, ce.id_parcial
                  ORDER BY m.nombre_materia, ce.id_parcial";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) return [];
        $stmt->bind_param("ii", $idAlumno, $idMaestro);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>