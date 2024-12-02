<?php
class Database {
    private $pdo;

    public function __construct($host, $db, $user, $password) {
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Ошибка подключения к базе данных: " . $e->getMessage());
        }
    }

    public function getPDO() {
        return $this->pdo;
    }
}
