<?php

/**

 * 
 */


require('credentials.php');

class Database
{   
    private $server = null;
    private $databasename = null;
    private $user = null;
    private $password = null;
    
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
            $dbconnection = new PDO('mysql:host=' . $this->server . ';dbname=' . $this->databasename, $this->user, $this->password);
            $dbconnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // For debuggin
            echo 'Database connection status: ' . $dbconnection->getAttribute(PDO::ATTR_CONNECTION_STATUS) ."\n";
            return $dbconnection;
        } catch (Exception $e) {
            echo ("Database Error: " . $e->getMessage());
        }
    }
}

// $db = new Database;
// $db->connect();

?>