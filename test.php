<?php

require_once 'includes/connection.php';

$db = Database::getConnection();

if ($db instanceof mysqli) {
    echo "✅ Conexión exitosa";
} else {
    echo "❌ No se pudo conectar";
}