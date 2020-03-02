<?php

/**
 * Database
 * 
 * @author Ville Kouhia
 * 
 * Handles databaseconnection
 * TODO: Easy way to change different database. PDO supported databases https://www.php.net/manual/en/pdo.drivers.php
 * 
 */

// All sensitive connection information is now handled by credentials.php which construcs credentials object. 
require('credentials.php');

class Database
{   
    private $server = null;
    private $databasename = null;
    private $user = null;
    private $password = null;
    private $dbconnection = null;
    
    public function __construct () 
    {
        $credentials = new credentials();
        $this->password = $credentials->getPassword();
        $this->user = $credentials->getDbUser();
        $this->databasename = $credentials->getDatabaseName();
        $this->server = $credentials->getServer();
    }
    

    public function connect()
    {
        try {
            $this->dbconnection = new PDO('mysql:host=' . $this->server . ';dbname=' . $this->databasename, $this->user, $this->password);
            $this->dbconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // For debuggin
            //echo 'Database connection status: ' . $dbconnection->getAttribute(PDO::ATTR_CONNECTION_STATUS) ."\n"; //debug
            return $this->dbconnection;
        } catch (Exception $e) {
            echo ("Database Error: " . $e->getMessage());
        }
    }
    
    public function disconnect() 
    {
        $this->dbconnection = null;
    }
    
}

// $db = new Database;
// $db->connect();

?>