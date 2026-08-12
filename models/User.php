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

        $password = password_hash($data['contrasena'], PASSWORD_DEFAULT);

        $idCarrera = (!empty($data['id_carrera']) && $data['id_carrera'] !== 'null') ? (int)$data['id_carrera'] : null;

        $stmt->bind_param(
            "iisssssssi",
            (int)$data['id_rol'],
            $idCarrera,
            $data['nombre'],
            $data['apellido_paterno'],
            $data['apellido_materno'],
            $data['usuario'],
            $password,
            $data['correo'] ?? null,
            $data['telefono'] ?? null,
            (int)($data['activo'] ?? 1)
        );

        return $stmt->execute();
    }

    public function update(int $id, array $data): bool
    {
        $idCarrera = (!empty($data['id_carrera']) && $data['id_carrera'] !== 'null') ? (int)$data['id_carrera'] : null;

        if (!empty($data['contrasena'])) {
            $this->updatePassword($id, $data['contrasena']);
        }

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

        $stmt->bind_param(
            "iissssssii",
            (int)$data['id_rol'],
            $idCarrera,
            $data['nombre'],
            $data['apellido_paterno'],
            $data['apellido_materno'],
            $data['usuario'],
            $data['correo'] ?? null,
            $data['telefono'] ?? null,
            (int)($data['activo'] ?? 1),
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

        $stmt->bind_param("si", $passwordHash, $id);
        return $stmt->execute();
    }

    /**
     * ELIMINAR FÍSICAMENTE un usuario de la base de datos
     * Esto también eliminará las asignaciones relacionadas (si las hay)
     */
    public function delete(int $id): bool
    {
        // Primero, verificar si el usuario tiene asignaciones (maestro)
        // Si tiene asignaciones, también se eliminarán por CASCADE
        $stmt = $this->db->prepare("DELETE FROM usuarios_unam WHERE id_usuario = ?");
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