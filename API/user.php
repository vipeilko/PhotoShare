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
    
    protected $userId;      // this userid
    protected $firstname;
    protected $lastname;
    protected $email;
    protected $permissions; // this user permission
    protected $roles;       // this user roles
    protected $allUsers;    // allusers for list
    protected $lastPerm;    //
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
                $this->permissions['permission'][$row["Type"]][$row["Descr"]]['label'] = $row["Label"];
                $this->permissions['permission'][$row["Type"]][$row["Descr"]]['authorized'] = $row['Authorized'];
                
                // roles, only add if authorized one of permissions
                if ( $row['Authorized'] == 1 ) {
                    $this->roles['role'][$row["Type"]] = 1; // role->role->user->1 == true
                }
            } else { //This should not be used anymore
                //$this->permissions[$row["Descr"]] = $row["Authorized"];
            }
            $i++;
        }
        
        $this->database = $db->disconnect(); 
    }
    /**
     * 
     * 
     * @param string $permtype
     * @param string $descr
     * @return boolean
     */
    public function checkPrivilige(string $perm_type, string $descr) 
    {
        if ( empty($this->permissions) ) {
            $this->getUserPermissions(true);
        }
        
        //echo("perm_type " . $perm_type . "\n"); //debug
        //echo("perm_Desc " . $descr . "\n"); //debug
        
        //print_r ($this->permissions);
        
        if ( $this->permissions['permission'][$perm_type][$descr]['authorized'] == 1 ) {
            return true;
        }
        return false;
    }
    
    /**
     * 
     */
    private function getAllUsers() 
    {
        if ( !$this->checkPrivilige(PERM_USER, PERM_DESCR_EDIT_USER) ) {
            return "User has no right to fetch all users";
            //$this->throwException(USER_HAS_NO_RIGHT, "User has no right to fetch all users");
        }
        
        $db = new Database();
        $this->database = $db->connect();
        
        $disabled = 0;
        
        $sql = "SELECT Id, FirstName, LastName, Email FROM users WHERE Disabled = :disabled";
        $userId = $this->userId;
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":disabled", $disabled);
        
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->allUsers['users'][$row['Id']]['id'] = $row['Id'];
            $this->allUsers['users'][$row['Id']]['firstname'] = $row['FirstName'];
            $this->allUsers['users'][$row['Id']]['lastname'] = $row['LastName'];
            $this->allUsers['users'][$row['Id']]['email'] = $row['Email'];
        }
        
        $this->database = $db->disconnect(); 
        
    }
    
    /**
     * 
     */
    private function getUserPermissionById(int $id)
    {
        if ( !$this->checkPrivilige(PERM_USER, PERM_DESCR_EDIT_USER) ) {
            return "User has no right to get user permissions";
            //$this->throwException(USER_HAS_NO_RIGHT, "User has no right to fetch user permissions");
        }
        
        $db = new Database();
        $this->database = $db->connect();
        
        $sql = "SELECT p.Id, pt.Type, p.Descr, p.Label, up.Authorized FROM 
                                                                        user_perm up, 
                                                                        permissions p, 
                                                                        permission_type pt WHERE 

                                                                        up.UserId = :userid AND 
                                                                        up.PermId = p.Id AND 
                                                                        pt.id = p.PermType
                
                                                                        ORDER BY p.Order asc";
        $userId = $id;
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":userid", $id);
        
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->lastPerm['permissions'][$row['Type']][$row['Descr']]['permid'] = $row['Id'];
            $this->lastPerm['permissions'][$row['Type']][$row['Descr']]['descr'] = $row['Descr'];
            $this->lastPerm['permissions'][$row['Type']][$row['Descr']]['label'] = $row['Label'];
            $this->lastPerm['permissions'][$row['Type']][$row['Descr']]['authorized'] = $row['Authorized'];
        }
        
        $this->database = $db->disconnect(); 
    }
    
    
    public function setUserInformation() {
        
    }
    
    /**
     * 
     * @param array $permissions
     * @return string
     */
    public function setUserPermissions($permissions) {

        //print_r($permissions);
        
        $perm_id = array();

        foreach ($permissions as $perm ) {
            foreach ( $perm as $item ) {
                foreach ( $item as $key => $value ) {
                    if ( $key == "permid" ) {
                        array_push($perm_id, $value);
                    }
                }
            }
        }
        
        /*$i = 0;
        array_walk_recursive($permissions, function ($item, $key) use($i) {
            //echo "$key : $item\n";
            if ( $key == "permid" ) {
                echo "$i : $item\n";
               $perm_id[$i] = $item;
               $i++;
            }
        });*/
        
        //print_r($perm_id); //debug
        
        $db = new Database();
        $this->database = $db->connect();
        
        $sql = "UPDATE user_perm SET Authorized = 0 WHERE UserId = :userid";
        
        $userId = $this->getUserId();
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":userid", $userId);
        
        $stmt->execute();
        
        foreach ($perm_id as $perm) {
            $sql = "UPDATE user_perm SET Authorized = 1 WHERE UserId = :userid AND PermId = :permid";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":userid", $userId);
            $stmt->bindParam(":permid", $perm);
            
            $stmt->execute();
            
        }
        $this->database = $db->disconnect(); 
        
        return("User permissions updated successfully!");
        
    }
    
    public function addUser($password) {
        
        $passwordHash = password_hash($password, PASSWORD_ARGON2ID, ['cost' => PASSWORD_COST]);
        $password = null;
        
        $db = new Database();
        $this->database = $db->connect();
        
        $disabled = 0;
        $date = new DateTime();
        $createdOn = $date->format('Y-m-d H:i:s');
        
        
        $sql = "INSERT INTO users (Email, Password, FirstName, LastName, Disabled, LastLogin, CreatedOn) VALUES (:email, :password, :firstname, :lastname, :disabled, :timedate, :timedate)";
        
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":password", $passwordHash);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        $stmt->bindParam(":disabled", $disabled);
        $stmt->bindParam(":timedate", $createdOn);
        
        $stmt->execute();
        
        
        $this->setUserId($this->database->lastInsertId());
        
        // echo ("New userid: " . $this->getUserId() ."\n"); // debug
        
        //get all available permissions
        $sql = "SELECT Id FROM permissions";
        $stmt = $this->database->prepare($sql);
        
        $stmt->execute();
        $perm_id = array();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            array_push($perm_id, $row['Id']);
        }
        //print_r($perm_id); //debug
        // then add to database without permit
        
        $userid = $this->getUserId();
        $authorized = 0;
        
        foreach ($perm_id as $perm) {
        //for ($i = 0; $i < count($perm_id); $i++ ) {
            $sql = "INSERT INTO user_perm (PermId, UserId, Authorized) VALUES (:permid, :userid, :authorized) ";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":userid", $userid);
            $stmt->bindParam(":permid", $perm);
            $stmt->bindParam(":authorized", $authorized);
            
            $stmt->execute();
            
        }
        
        
                 
    }
    
    public function checkEmail(string $email) 
    {
        $db = new Database();
        $this->database = $db->connect();
        
        $sql = "SELECT Email FROM users WHERE Email = :email";
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":email", $email);
        
        $stmt->execute();
        $count = $stmt->rowCount();
        
        // if only one email is found return true, else something is wrong
        if ( $count == 1 ) {
            return true;
        } else {
            return false;
        }
    }
    
    public function updateBasicInformation(string $password = null) 
    {
        
        $db = new Database();
        $this->database = $db->connect();
        
        if ( !$password == null ) {
            $sql = "UPDATE users SET password = :password WHERE Id = :userid";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":userid", $this->userId);
            $stmt->bindParam(":password", $password);
            
            $stmt->execute();
        }
        
        $sql = "UPDATE users SET Email = :email, FirstName = :firstname, LastName = :lastname WHERE Id = :userid";
        
        $stmt = $this->database->prepare($sql);
        $stmt->bindParam(":userid", $this->userId);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":firstname", $this->firstname);
        $stmt->bindParam(":lastname", $this->lastname);
        
        $stmt->execute();
        
    }
    
    public function setEmail($email) 
    {
        $this->email = $email;
    }
    
    public function setLastName($lastname) 
    {
        $this->lastname = $lastname;
    }
    
    public function SetFirstName($firstname) 
    {
        $this->firstname = $firstname;
    }
    
    public function getUserPerm(int $id) 
    {
        $this->getUserPermissionById($id);
        return $this->lastPerm;
    }
    
    public function getUsers() 
    {
        $this->getAllUsers();
        return $this->allUsers;
    }
    
    public function printAllUsers() 
    {
        print_r ($this->allUsers);
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
     * @return string
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