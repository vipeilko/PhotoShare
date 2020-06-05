<?php

class credentials {
    
    private $server = '<your db server>';
    private $databasename = '<your db name>';
    private $user = '<db username>';
    private $dBpassword = '<db user password>';
    
    public function __construct() 
    {
        
    }
    
    function getPassword() 
    {
        return $this->dBpassword;
    }
    
    function getServer()
    {
        return $this->server;
    }

    function getDatabaseName()
    {
        return $this->databasename;
    }
    function getDbUser()
    {
        return $this->user;
    }
    
}

?>