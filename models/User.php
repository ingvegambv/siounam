<?php
// models/User.php

require_once __DIR__ . '/../includes/connection.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT
                u.id_usuario,
                u.nombre,
                u.apellido_paterno,
                u.apellido_materno,
                u.usuario,
                u.correo,
                u.telefono,
                r.nombre_rol,
                u.id_rol,
                c.nombre_carrera,
                u.activo,
                u.id_carrera
            FROM usuarios_unam u
            INNER JOIN rol r
                ON u.id_rol = r.id_rol
            LEFT JOIN carrera_unam c
                ON u.id_carrera = c.id_carrera
            ORDER BY u.nombre
        ";

        $result = $this->db->query($sql);

        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById(int $id)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios_unam WHERE id_usuario = ?"
        );

        $stmt->bind_param("i", $id);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByUsername(string $username)
    {
        $stmt = $this->db->prepare(
            "SELECT *
            FROM usuarios_unam
            WHERE usuario = ?
            AND activo = 1"
        );

        $stmt->bind_param("s", $username);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function create(array $data): bool
    {
        if (empty($data['contrasena'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO usuarios_unam
            (
                id_rol,
                id_carrera,
                nombre,
                apellido_paterno,
                apellido_materno,
                usuario,
                contrasena,
                correo,
                telefono,
                activo
            )
            VALUES
            (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        if (!$stmt) {
            return false;
        }

        $password = password_hash($data['contrasena'], PASSWORD_DEFAULT);

        // Convertir valores para evitar null en bind_param
        $idRol = (int)$data['id_rol'];
        $idCarrera = (!empty($data['id_carrera']) && $data['id_carrera'] !== 'null') ? (int)$data['id_carrera'] : null;
        $nombre = $data['nombre'];
        $apellidoPaterno = $data['apellido_paterno'];
        $apellidoMaterno = $data['apellido_materno'];
        $usuario = $data['usuario'];
        $correo = !empty($data['correo']) ? $data['correo'] : null;
        $telefono = !empty($data['telefono']) ? $data['telefono'] : null;
        $activo = (int)($data['activo'] ?? 1);

        $stmt->bind_param(
            "iisssssssi",
            $idRol,
            $idCarrera,
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $usuario,
            $password,
            $correo,
            $telefono,
            $activo
        );

        $result = $stmt->execute();
        
        // Si es maestro, asignar carreras
        if ($result && $idRol == 3 && isset($data['maestro_carreras'])) {
            $userId = $this->db->insert_id;
            $this->assignMaestroCarreras($userId, $data['maestro_carreras']);
        }
        
        return $result;
    }

    public function update(int $id, array $data): bool
    {
        // Si se proporciona nueva contraseña, actualizarla
        if (!empty($data['contrasena'])) {
            $this->updatePassword($id, $data['contrasena']);
        }

        $idCarrera = (!empty($data['id_carrera']) && $data['id_carrera'] !== 'null') ? (int)$data['id_carrera'] : null;

        $stmt = $this->db->prepare(
            "UPDATE usuarios_unam
            SET
                id_rol = ?,
                id_carrera = ?,
                nombre = ?,
                apellido_paterno = ?,
                apellido_materno = ?,
                usuario = ?,
                correo = ?,
                telefono = ?,
                activo = ?
            WHERE id_usuario = ?"
        );

        if (!$stmt) {
            return false;
        }

        $idRol = (int)$data['id_rol'];
        $nombre = $data['nombre'];
        $apellidoPaterno = $data['apellido_paterno'];
        $apellidoMaterno = $data['apellido_materno'];
        $usuario = $data['usuario'];
        $correo = !empty($data['correo']) ? $data['correo'] : null;
        $telefono = !empty($data['telefono']) ? $data['telefono'] : null;
        $activo = (int)($data['activo'] ?? 1);

        $stmt->bind_param(
            "iissssssii",
            $idRol,
            $idCarrera,
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $usuario,
            $correo,
            $telefono,
            $activo,
            $id
        );

        $result = $stmt->execute();
        
        // Si es maestro, actualizar carreras asignadas
        if ($result && $idRol == 3 && isset($data['maestro_carreras'])) {
            // Eliminar asignaciones anteriores
            $this->clearMaestroCarreras($id);
            // Asignar nuevas carreras
            $this->assignMaestroCarreras($id, $data['maestro_carreras']);
        } elseif ($result && $idRol != 3) {
            // Si ya no es maestro, eliminar sus asignaciones
            $this->clearMaestroCarreras($id);
        }

        return $result;
    }

    public function updatePassword(int $id, string $password): bool
    {
        if (empty($password)) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare(
            "UPDATE usuarios_unam
            SET contrasena = ?
            WHERE id_usuario = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("si", $passwordHash, $id);
        return $stmt->execute();
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE usuarios_unam
            SET activo = 0
            WHERE id_usuario = ?"
        );

        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function login(string $usuario, string $password)
    {
        $user = $this->getByUsername($usuario);

        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['contrasena'])) {
            return false;
        }

        unset($user['contrasena']);

        return $user;
    }

    /**
     * Obtiene usuarios con filtro de carrera (para coordinador)
     */
    public function getByCarrera($idCarrera, $rol = null) {
        $sql = "SELECT u.*, r.nombre_rol, c.nombre_carrera 
                FROM usuarios_unam u
                INNER JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN carrera_unam c ON u.id_carrera = c.id_carrera
                WHERE u.activo = 1";
        
        $params = [];
        $types = "";
        
        if ($idCarrera) {
            $sql .= " AND (u.id_carrera = ? 
                      OR u.id_usuario IN (
                          SELECT id_usuario FROM maestro_carrera WHERE id_carrera = ?
                      ))";
            $params[] = $idCarrera;
            $params[] = $idCarrera;
            $types .= "ii";
        }
        
        if ($rol) {
            $sql .= " AND u.id_rol = ?";
            $params[] = $rol;
            $types .= "i";
        }
        
        $sql .= " ORDER BY u.apellido_paterno, u.nombre";
        
        $stmt = $this->db->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Obtiene solo maestros de una carrera específica
     */
    public function getMaestrosByCarrera($idCarrera) {
        $query = "SELECT DISTINCT u.*, r.nombre_rol 
                  FROM usuarios_unam u
                  INNER JOIN rol r ON u.id_rol = r.id_rol
                  INNER JOIN maestro_carrera mc ON u.id_usuario = mc.id_usuario
                  WHERE u.id_rol = 3 
                    AND u.activo = 1
                    AND mc.id_carrera = ?
                  ORDER BY u.apellido_paterno, u.nombre";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idCarrera);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Asigna carreras a un maestro
     */
    private function assignMaestroCarreras($idUsuario, $carreras) {
        if (!is_array($carreras) || empty($carreras)) {
            return;
        }
        
        $query = "INSERT INTO maestro_carrera (id_usuario, id_carrera) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);
        
        foreach ($carreras as $idCarrera) {
            $stmt->bind_param("ii", $idUsuario, $idCarrera);
            $stmt->execute();
        }
    }

    /**
     * Elimina todas las asignaciones de carrera de un maestro
     */
    private function clearMaestroCarreras($idUsuario) {
        $query = "DELETE FROM maestro_carrera WHERE id_usuario = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
    }
}