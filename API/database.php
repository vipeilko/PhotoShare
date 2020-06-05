<?php

/**
 * Database
 * 
 * @author Ville Kouhia
 * @version 1.0
 * 
 * Handles databaseconnection
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
    
    /**
     * __construct gets information from credentials to db connection
     * 
     * To change database edit credentials.php
     * 
     */
    public function __construct () 
    {
        $credentials = new credentials();
        $this->password = $credentials->getPassword();
        $this->user = $credentials->getDbUser();
        $this->databasename = $credentials->getDatabaseName();
        $this->server = $credentials->getServer();
    }
    
    /**
     * Database->connect
     * 
     * Connects to defined database 
     * 
     * @return unknown
     */
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
    
    /**
     *  Database->disconnect
     *  
     *  Disconnects connection from database
     */
    public function disconnect() 
    {
        $this->dbconnection = null;
    }
    
}

// $db = new Database;
// $db->connect();

?>