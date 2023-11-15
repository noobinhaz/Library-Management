<?php
namespace core;

use Core\Config;

class DB
{
    protected $conn;

    public function connect()
    {
        $config = Config::$db;

        if ($this->conn) {
            return $this->conn; 
        }

        $this->conn = new \mysqli(
            $config['host'],
            $config['username'],
            $config['password'],
            $config['database'],
            $config['port']
        );

        if ($this->conn->connect_error) {
            die('Connect Error (' . $this->conn->connect_errno . ') ' . $this->conn->connect_error);
        }
        
        return $this->conn;
    }
}
