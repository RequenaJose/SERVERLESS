<?php
namespace Infrastructure\Configuration;
use PDO;

class Database {
    public static function getConnection(): PDO {
        $dsn = "pgsql:host=localhost;port=5432;dbname=SAER;";
        return new PDO($dsn, "postgres", "postgres", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    }
}