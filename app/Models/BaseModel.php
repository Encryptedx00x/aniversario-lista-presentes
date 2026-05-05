<?php
declare(strict_types=1);

namespace App\Models;

class BaseModel {
    protected \PDO $db;

    public function __construct() {
        static $pdo = null;
        if ($pdo === null) {
            $cfg = require dirname(__DIR__, 2) . '/config/database.php';
            $dsn = "mysql:host={$cfg['host']};dbname={$cfg['name']};charset=utf8mb4";
            $pdo = new \PDO($dsn, $cfg['user'], $cfg['pass'], [
                \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        $this->db = $pdo;
    }

    public function getDb(): \PDO {
        return $this->db;
    }

    protected function query(string $sql, array $params = []): \PDOStatement {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}
