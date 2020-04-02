<?php
/**
 *  User.php
 *  
 *  A class to handle users and permissions
 *  
 * @author Ville
 *
 * 02.04.2020 Integrated to api
 * 
 *
 */

//require('settings.php'); // for debuggin only
//require('database.php'); // for debuggin only 


class user {
    
    protected $userId;
    protected $permissions;
    protected $roles;
    public $database;
    
    
    public function __construct() 
    {
        $this->permissions = array();
        $this->roles = array();
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
     * 
     */
    public function getUserRoles() 
    {
        $this->getUserPermissions();
    }
    
    /**
     * gets user permissions to array, format 
     *  [permission_name] = Label
     *  for example 
     *  Array (
     *      [user] => Array
     *      (
     *          [add] => add users
     *          [edit] => edit users
     *          [delete] => delete users
     *          [Label] => user permissions
     *      )
     *  )
     */
    public function getUserPermissions($grouped = true) 
    {
        $db = new Database();
        $this->database = $db->connect();
        
        //clear old permissions to avoid mixing these
        $this->permissions = array();
        
        $authorized = 1;
        
        $sql = "SELECT up.PermId, up.Authorized, p.Descr, pt.Type, p.Label FROM user_perm up, permissions p, permission_type pt WHERE  
                                                                              up.UserId = :userId AND 
                                                                              up.PermId = p.Id AND

                                                                              pt.Id = p.PermType";
        $userId = $this->userId;
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":userId", $userId);
        //$stmt->bindParam(":authorized", $authorized); //return only perms that user has
        $stmt->execute();
        
        $i = 0;
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ( $grouped ) {
                //permissions
                $this->permissions[$row["Type"]][$row["Descr"]]['label'] = $row["Label"];
                $this->permissions[$row["Type"]][$row["Descr"]]['authorized'] = $row['Authorized'];
                
                // roles, only add if authorized one of permissions
                if ( $row['Authorized'] == 1 ) {
                    $this->roles['role'][$row["Type"]] = 1; // role->user->1 == true
                }
            } else { //This should not be used anymore
                //$this->permissions[$row["Descr"]] = $row["Authorized"];
            }
            $i++;
        }
        
        $this->database = $db->disconnect(); 
    }

    public function printPermissions()
    {
        print_r ($this->permissions);
    }
    public function printRoles()
    {
        print_r ($this->roles);
    }

    /**
     *
     * @return Json string
     */
    public function getRoles()
    {
        $this->getUserRoles(true);
        return $this->roles;
    }
    /**
     *
     * @return Json string
     */
    public function getRolesJson()
    {
        $this->getUserRoles(true);
        return json_encode($this->roles);
    }
    /**
     * 
     * @return Json string
     */
    public function getPermissionsJson() 
    {
        $this->getUserPermissions(true);
        return json_encode($this->permissions);
    }
    /**
     *
     * @return Json string
     */
    public function getPermissions()
    {
        $this->getUserPermissions(true);
        return $this->permissions;
    }
}

// for testing
/*
$u = new user();
$u->setUserId(2);
$u->getUserRoles();
$u->printRoles();
$u->printPermissions();
*/
/*$u->getUserPermissions();
$u->printPermissions();

echo($u->getPermissionsJson());
*/

?>