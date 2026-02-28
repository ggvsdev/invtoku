<?php
declare(strict_types=1);

require_once __DIR__ . '/constants.php';

if (!class_exists('Database')) {

class Database {
    private static ?PDO $instance = null;
    
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            try {
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES " . DB_CHARSET
                ]);
            } catch (PDOException $e) {
                error_log("Error de conexión BD: " . $e->getMessage());
                throw new Exception("Error de conexión a la base de datos. Verifique configuración.");
            }
        }
        return self::$instance;
    }
    
    private function __clone() {}
}

}