<?php
// models/Carrera.php

require_once __DIR__ . '/../includes/connection.php';

class Carrera
{
    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getAll(): array
    {
        $result = $this->db->query(
            "SELECT id_carrera, nombre_carrera 
            FROM carrera_unam 
            ORDER BY nombre_carrera"
        );
        
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
}