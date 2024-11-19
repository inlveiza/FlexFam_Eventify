<?php

define("SERVER", "localhost");
define("DBASE", "eventify");
define("USER", "root");
define("PASSWORD", "");
define("SECRET_KEY", "B4rb13LaT");

class Connection {
    public $pdo;

    public function connect() {
        $this->pdo = null;
        try {
        	//$this->pdo = new \PDO("mysql:host=" . SERVER . ";dbname=" . DBASE, USER, PASSWORD);
            $this->pdo = new \PDO("mysql:unix_socket=/data/data/com.termux/files/usr/var/run/mysqld/mysqld.sock;dbname=" . DBASE, USER, PASSWORD);
            $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            
        } catch (\PDOException $e) {
            // Handle error: log it and return a null connection
            error_log("Database connection error: " . $e->getMessage());
            return null; // or handle it as per your application's needs
        }
        return $this->pdo;
    }
}
