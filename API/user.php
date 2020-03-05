<?php
/**
 * 
 * @author Ville
 *
 */

require('settings.php');
require('database.php');

class user {
    
    
    protected $userId;
    protected $permissions;
    public $database;
    
    
    public function __construct() 
    {
        
        $this->permissions = array();
    }
    
    /**
     * 
     */
    public function login() 
    {
       //TODO: proper login function 
    }
    
    public function setUserId(int $userid) {
        $this->userId = $userid;
    }
    
    public function getUserId()
    {
        return $this->userId;
    }
    
    /**
     * gets user permissions to array, format 
     *  [permission_name] = boolean
     *  for example 
     *  array (
     *      [addUser]  => 1
     *      [editUser] => 1
     *      )
     */
    public function getUserPermissions($grouped = false) 
    {
        $db = new Database();
        $this->database = $db->connect();
        
        //clear old permissions to avoid mixing these
        $this->permissions = array();
        
        $sql = "SELECT up.PermId, up.Authorized, p.Descr, pt.Type FROM user_perm up, permissions p, permission_type pt WHERE  
                                                                  up.UserId = :userId AND 
                                                                  up.PermId = p.Id AND
                                                                  pt.Id = p.PermType";
        $userId = $this->userId;
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":userId", $userId);
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($grouped) {
                $this->permissions[$row["Type"]][$row["Descr"]] = $row["Authorized"];
            } else {
                $this->permissions[$row["Descr"]] = $row["Authorized"];
            }
        }
        
        $this->database = $db->disconnect(); 
    }

    public function printPermissions()
    {
        print_r ($this->permissions);
    }

}

// test
$u = new user();
$u->setUserId(2);
$u->getUserPermissions();
$u->printPermissions();

$u->getUserPermissions(true);
$u->printPermissions();
?>