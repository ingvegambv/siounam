<?php
// models/User.php

require_once __DIR__ . '/../includes/connection.php';

class User
{
    private mysqli $db;

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

        // Si id_carrera es null, usar NULL en la base de datos
        // Para bind_param, si es null, debemos pasarlo como string vacío y usar 's' o null
        // La solución: usar un valor por defecto y manejarlo correctamente
        
        // CORRECCIÓN: Usamos una variable separada para el tipo de dato
        $tipoCarrera = $idCarrera === null ? 's' : 'i';
        $valorCarrera = $idCarrera === null ? null : $idCarrera;

        // Usamos bind_param con todos los parámetros como variables
        // IMPORTANTE: Todos los parámetros deben ser variables, no valores directos
        $stmt->bind_param(
            "iisssssssi",
            $idRol,
            $idCarrera,  // Esto puede ser null, pero bind_param lo maneja con 'i'
            $nombre,
            $apellidoPaterno,
            $apellidoMaterno,
            $usuario,
            $password,
            $correo,
            $telefono,
            $activo
        );

        return $stmt->execute();
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

        return $stmt->execute();
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
}