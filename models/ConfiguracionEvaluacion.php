<?php
// models/ConfiguracionEvaluacion.php

require_once __DIR__ . '/../includes/connection.php';

class ConfiguracionEvaluacion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Obtiene configuración por asignación y parcial
     */
    public function getByAsignacionParcial($idAsignacion, $idParcial)
    {
        $query = "SELECT ce.*, 
                         (SELECT COUNT(*) FROM aspectos_evaluacion WHERE id_configuracion = ce.id_configuracion) as total_aspectos,
                         (SELECT SUM(porcentaje) FROM aspectos_evaluacion WHERE id_configuracion = ce.id_configuracion) as suma_porcentajes
                  FROM configuracion_evaluacion ce
                  WHERE ce.id_asignacion = ? AND ce.id_parcial = ?";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) return null;
        $stmt->bind_param("ii", $idAsignacion, $idParcial);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    /**
     * Obtiene configuración con aspectos
     */
    public function getConfiguracionCompleta($idAsignacion, $idParcial)
    {
        $config = $this->getByAsignacionParcial($idAsignacion, $idParcial);
        if (!$config) return null;

        $query = "SELECT * FROM aspectos_evaluacion 
                  WHERE id_configuracion = ? 
                  ORDER BY orden";
        $stmt = $this->db->prepare($query);
        if (!$stmt) {
            $config['aspectos'] = [];
            return $config;
        }
        $stmt->bind_param("i", $config['id_configuracion']);
        $stmt->execute();
        $config['aspectos'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return $config;
    }

    /**
     * Crear o actualizar configuración
     */
    public function guardarConfiguracion($data)
    {
        $idAsignacion = (int)$data['id_asignacion'];
        $idParcial = (int)$data['id_parcial'];
        $numeroParciales = (int)$data['numero_parciales'];
        $totalClases = (int)$data['total_clases'];
        $limiteFaltas = (int)($totalClases * 0.2);

        // Verificar si existe
        $existing = $this->getByAsignacionParcial($idAsignacion, $idParcial);

        if ($existing) {
            // Actualizar
            $query = "UPDATE configuracion_evaluacion 
                      SET numero_parciales = ?, total_clases = ?, limite_faltas = ?
                      WHERE id_configuracion = ?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) return false;
            $stmt->bind_param("iiii", $numeroParciales, $totalClases, $limiteFaltas, $existing['id_configuracion']);
            return $stmt->execute();
        } else {
            // Insertar
            $query = "INSERT INTO configuracion_evaluacion 
                      (id_asignacion, id_parcial, numero_parciales, total_clases, limite_faltas) 
                      VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            if (!$stmt) return false;
            $stmt->bind_param("iiiii", $idAsignacion, $idParcial, $numeroParciales, $totalClases, $limiteFaltas);
            return $stmt->execute();
        }
    }

    /**
     * Agregar aspecto a la configuración
     */
    public function agregarAspecto($idConfiguracion, $nombre, $porcentaje, $orden = null)
    {
        if ($orden === null) {
            $query = "SELECT MAX(orden) as max_orden FROM aspectos_evaluacion WHERE id_configuracion = ?";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                $orden = 0;
            } else {
                $stmt->bind_param("i", $idConfiguracion);
                $stmt->execute();
                $result = $stmt->get_result()->fetch_assoc();
                $orden = ($result['max_orden'] ?? -1) + 1;
            }
        }

        $query = "INSERT INTO aspectos_evaluacion (id_configuracion, nombre, porcentaje, orden) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("isdi", $idConfiguracion, $nombre, $porcentaje, $orden);
        return $stmt->execute();
    }

    /**
     * Eliminar aspecto
     */
    public function eliminarAspecto($idAspecto)
    {
        $query = "DELETE FROM aspectos_evaluacion WHERE id_aspecto = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("i", $idAspecto);
        return $stmt->execute();
    }

    /**
     * Actualizar aspecto
     */
    public function actualizarAspecto($idAspecto, $nombre, $porcentaje)
    {
        $query = "UPDATE aspectos_evaluacion SET nombre = ?, porcentaje = ? WHERE id_aspecto = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("sdi", $nombre, $porcentaje, $idAspecto);
        return $stmt->execute();
    }

    /**
     * Bloquear configuración
     */
    public function bloquear($idConfiguracion)
    {
        $query = "UPDATE configuracion_evaluacion SET bloqueado = 1 WHERE id_configuracion = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("i", $idConfiguracion);
        return $stmt->execute();
    }

    /**
     * Desbloquear configuración
     */
    public function desbloquear($idConfiguracion)
    {
        $query = "UPDATE configuracion_evaluacion SET bloqueado = 0 WHERE id_configuracion = ?";
        $stmt = $this->db->prepare($query);
        if (!$stmt) return false;
        $stmt->bind_param("i", $idConfiguracion);
        return $stmt->execute();
    }

    /**
     * Obtener configuración con validación de porcentaje 100%
     */
    public function getConfiguracionConValidacion($idAsignacion, $idParcial)
    {
        $config = $this->getConfiguracionCompleta($idAsignacion, $idParcial);
        if ($config) {
            $suma = 0;
            foreach ($config['aspectos'] as $aspecto) {
                $suma += $aspecto['porcentaje'];
            }
            $config['suma_porcentajes'] = $suma;
            $config['porcentaje_completo'] = ($suma == 100);
            $config['puede_bloquear'] = ($suma == 100 && count($config['aspectos']) > 0);
        }
        return $config;
    }

    /**
     * Obtiene todas las configuraciones de una asignación
     */
    public function getConfiguracionesByAsignacion($idAsignacion)
    {
        $query = "SELECT ce.*, 
                         (SELECT SUM(porcentaje) FROM aspectos_evaluacion WHERE id_configuracion = ce.id_configuracion) as suma_porcentajes,
                         (SELECT COUNT(*) FROM aspectos_evaluacion WHERE id_configuracion = ce.id_configuracion) as total_aspectos
                  FROM configuracion_evaluacion ce
                  WHERE ce.id_asignacion = ?
                  ORDER BY ce.id_parcial";
        
        $stmt = $this->db->prepare($query);
        if (!$stmt) return [];
        $stmt->bind_param("i", $idAsignacion);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>