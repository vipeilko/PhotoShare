<?php

/**

 * 
 */
class Database
{

    private $server = 'exampleHost';
    private $databasename = 'exampleDbName';
    private $user = 'exampleDbName';
    private $password = 'examplePass';

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