<?php

define("SERVER", "localhost");
define("DBASE", "eventify");
define("USER", "root");
define("PASSWORD", "");
define("SECRET_KEY", "B4rb1eL4T");

class Connection {
	//protected $con_string = "mysql:unix_socket=/data/data/com.termux/files/usr/var/run/mysqld/mysqld.sock;dbname=" . DBASE . ";charset=utf8mb4";
    protected $con_string = "mysql:host=" . SERVER . ";dbname=" . DBASE . ";charset=utf8mb4";
    protected $options = [
        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
        \PDO::ATTR_EMULATE_PREPARES => false  
    ];

    public function connect() {
        try {
            // Create a new PDO instance
            $pdo = new \PDO($this->con_string, USER, PASSWORD, $this->options);
            //echo "Connection successful!"; // Debugging line
            return $pdo; // Return the PDO instance
        } catch (\PDOException $e) {
            // Handle the connection error
            echo "Connection failed: " . $e->getMessage();
            return null; // Return null if connection fails
        }
    }
}
