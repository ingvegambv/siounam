 <?php

require_once __DIR__ . '/../config/database.php';

class Database
{
    private static ?mysqli $connection = null;

    private function __construct() {}
    private function __clone() {}

    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {

            try {

                self::$connection = new mysqli(
                    DB_HOST,
                    DB_USER,
                    DB_PASS,
                    DB_NAME,
                    DB_PORT
                );

                if (self::$connection->connect_error) {
                    throw new Exception(self::$connection->connect_error);
                }

                self::$connection->set_charset("utf8mb4");

            } catch (Exception $e) {

                die("Error de conexión: " . $e->getMessage());

            }
        }

        return self::$connection;
    }
}