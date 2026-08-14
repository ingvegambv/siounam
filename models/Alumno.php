<?php
// models/Alumno.php

require_once __DIR__ . '/../includes/connection.php';

class Alumno
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene todos los alumnos
     */
    public function getAll()
    {
        $query = "SELECT a.*, c.nombre_carrera, g.nombre_grupo 
                  FROM alumnos_unam a
                  INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  ORDER BY a.matricula";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene un alumno por ID
     */
    public function getById($id)
    {
        $query = "SELECT a.*, c.nombre_carrera, g.nombre_grupo 
                  FROM alumnos_unam a
                  INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  WHERE a.id_alumno = ?";
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
     * Obtiene un alumno por matrícula
     */
    public function getByMatricula($matricula)
    {
        $query = "SELECT a.*, c.nombre_carrera, g.nombre_grupo 
                  FROM alumnos_unam a
                  INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  WHERE a.matricula = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    /**
     * Verifica si una matrícula ya existe
     */
    public function matriculaExists($matricula, $excludeId = null)
    {
        $query = "SELECT COUNT(*) FROM alumnos_unam WHERE matricula = ?";
        $params = [$matricula];
        $types = "s";
        
        if ($excludeId) {
            $query .= " AND id_alumno != ?";
            $params[] = $excludeId;
            $types .= "i";
        }
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        return $result[0] > 0;
    }

    /**
     * Busca alumnos por matrícula o nombre (NUEVO MÉTODO)
     */
    public function search($query, $idCarrera = null, $limit = 20)
    {
        $sql = "SELECT a.*, c.nombre_carrera, g.nombre_grupo 
                FROM alumnos_unam a
                INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
                INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                WHERE (a.matricula LIKE ? 
                       OR a.nombre LIKE ? 
                       OR a.apellido_paterno LIKE ? 
                       OR a.apellido_materno LIKE ?
                       OR CONCAT(a.nombre, ' ', a.apellido_paterno) LIKE ?)";
        
        $searchTerm = "%$query%";
        $params = [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm];
        $types = "sssss";
        
        if ($idCarrera) {
            $sql .= " AND a.id_carrera = ?";
            $params[] = $idCarrera;
            $types .= "i";
        }
        
        $sql .= " ORDER BY a.matricula LIMIT ?";
        $params[] = $limit;
        $types .= "i";
        
        $stmt = $this->db->prepare($sql);
        if (!$stmt) {
            error_log("Error en search: " . $this->db->error);
            return [];
        }
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene alumnos por carrera
     */
    public function getByCarrera($idCarrera)
    {
        $query = "SELECT a.*, c.nombre_carrera, g.nombre_grupo 
                  FROM alumnos_unam a
                  INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  WHERE a.id_carrera = ?
                  ORDER BY a.matricula";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene alumnos por grupo
     */
    public function getByGrupo($idGrupo)
    {
        $query = "SELECT a.*, c.nombre_carrera, g.nombre_grupo 
                  FROM alumnos_unam a
                  INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  WHERE a.id_grupounam = ?
                  ORDER BY a.matricula";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $idGrupo);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Cuenta alumnos por grupo en una carrera
     */
    public function countByGrupo($idCarrera)
    {
        $query = "SELECT g.id_grupounam, g.nombre_grupo, COUNT(a.id_alumno) as total 
                  FROM grupo_unam g
                  LEFT JOIN alumnos_unam a ON g.id_grupounam = a.id_grupounam
                  WHERE g.id_carrera = ?
                  GROUP BY g.id_grupounam
                  ORDER BY g.nombre_grupo";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene alumnos recientes por carrera
     */
    public function getRecentByCarrera($idCarrera, $limit = 10)
    {
        $query = "SELECT a.*, g.nombre_grupo 
                  FROM alumnos_unam a
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  WHERE a.id_carrera = ?
                  ORDER BY a.id_alumno DESC
                  LIMIT ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("ii", $idCarrera, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene el semestre actual de un alumno
     */
    public function getSemestreActual($idAlumno) {
        $query = "SELECT DISTINCT m.id_semestre, s.nombre_semestre
                  FROM alumnos_unam a
                  INNER JOIN grupo_unam g ON a.id_grupounam = g.id_grupounam
                  INNER JOIN materias m ON m.id_carrera = a.id_carrera
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  WHERE a.id_alumno = ?
                  ORDER BY m.id_semestre DESC
                  LIMIT 1";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene las materias de un alumno por semestre específico
     */
    public function getMateriasByAlumnoSemestre($idAlumno, $idSemestre) {
        $query = "SELECT m.*, s.nombre_semestre,
                  (SELECT CONCAT(u.nombre, ' ', u.apellido_paterno) 
                   FROM asignacion_maestros am
                   INNER JOIN usuarios_unam u ON am.id_usuario = u.id_usuario
                   WHERE am.id_materia = m.id_materia 
                   AND am.id_grupounam = a.id_grupounam
                   LIMIT 1) as maestro
                  FROM alumnos_unam a
                  INNER JOIN materias m ON m.id_carrera = a.id_carrera
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  WHERE a.id_alumno = ? AND m.id_semestre = ?
                  ORDER BY m.nombre_materia";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("ii", $idAlumno, $idSemestre);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene todos los semestres con materias de un alumno
     */
    public function getSemestresWithMaterias($idAlumno) {
        $query = "SELECT DISTINCT m.id_semestre, s.nombre_semestre
                  FROM alumnos_unam a
                  INNER JOIN materias m ON m.id_carrera = a.id_carrera
                  INNER JOIN semestre s ON m.id_semestre = s.id_semestre
                  WHERE a.id_alumno = ?
                  ORDER BY m.id_semestre";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("i", $idAlumno);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Crea un nuevo alumno
     */
    public function create($data)
    {
        // Validar que la matrícula no exista
        if ($this->matriculaExists($data['matricula'])) {
            return false;
        }

        $query = "INSERT INTO alumnos_unam (matricula, id_carrera, id_grupounam, nombre, apellido_paterno, apellido_materno) 
                  VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            error_log("Error preparando query: " . $this->db->error);
            return false;
        }
        
        $matricula = $data['matricula'];
        $idCarrera = (int)$data['id_carrera'];
        $idGrupo = (int)$data['id_grupounam'];
        $nombre = $data['nombre'];
        $apellidoPaterno = $data['apellido_paterno'];
        $apellidoMaterno = $data['apellido_materno'];
        
        $stmt->bind_param(
            "siisss",
            $matricula,
            $idCarrera,
            $idGrupo,
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno
        );
        
        $result = $stmt->execute();
        if (!$result) {
            error_log("Error ejecutando query: " . $stmt->error);
        }
        return $result;
    }

    /**
     * Actualiza un alumno
     */
    public function update($id, $data)
    {
        // Validar que la matrícula no exista (excepto para este alumno)
        if ($this->matriculaExists($data['matricula'], $id)) {
            return false;
        }

        $query = "UPDATE alumnos_unam 
                  SET matricula = ?, id_carrera = ?, id_grupounam = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?
                  WHERE id_alumno = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            error_log("Error preparando query update: " . $this->db->error);
            return false;
        }
        
        $matricula = $data['matricula'];
        $idCarrera = (int)$data['id_carrera'];
        $idGrupo = (int)$data['id_grupounam'];
        $nombre = $data['nombre'];
        $apellidoPaterno = $data['apellido_paterno'];
        $apellidoMaterno = $data['apellido_materno'];
        
        $stmt->bind_param(
            "siisssi",
            $matricula,
            $idCarrera,
            $idGrupo,
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $id
        );
        
        $result = $stmt->execute();
        if (!$result) {
            error_log("Error ejecutando query update: " . $stmt->error);
        }
        return $result;
    }

    /**
     * Elimina un alumno
     */
    public function delete($id)
    {
        $query = "DELETE FROM alumnos_unam WHERE id_alumno = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    /**
     * Migra alumnos entre grupos
     */
    public function migrar($grupoOrigen, $grupoDestino)
    {
        $this->db->begin_transaction();
        
        try {
            // Verificar que exista el grupo destino
            $query = "SELECT id_grupounam FROM grupo_unam WHERE id_grupounam = ?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Error al verificar grupo destino");
            }
            $stmt->bind_param("i", $grupoDestino);
            $stmt->execute();
            if (!$stmt->get_result()->fetch_assoc()) {
                throw new Exception("Grupo destino no existe");
            }
            
            // Migrar alumnos
            $query = "UPDATE alumnos_unam SET id_grupounam = ? WHERE id_grupounam = ?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                throw new Exception("Error al preparar migración");
            }
            $stmt->bind_param("ii", $grupoDestino, $grupoOrigen);
            $stmt->execute();
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Error en migración: " . $e->getMessage());
            return false;
        }
    }
}
?>