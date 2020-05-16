<?php
/**
 * 
 * @version 0.0.1
 * @author Ville Kouhia
 * 
 * Changelog
 *  + 15.02.2020 First implementation
 *  + 16.02.2020 Cleaned code a bit and more comments
 *  + 17.02.2020 Added refresh token generation in generateToken
 *               Have to decide if both tokens are generated at same time. And if not, 
 *               will we start new period of AGE_OF_REFRESH_TOKEN or should we get original time 
 *               from database.
 *               Moved db connection introduction from __construct to function.
 *  + 24.02.2020 Added password hashing using built-in password_hash function
 *  + 02.04.2020 New functions getRoles and getPermissions
 *  + 12.04.2020 add and edit user
 *  + 24.04.2020 A proper introduction of codes
 *  
 */


class Api extends Rest
{
    protected qr $qr;
    
    public $database;

    
    public function __construct()
    {
        parent::__construct();
        $this->qr = new qr();

    }

    /**
     * generateToken
     * 
     * Will always generate refreshToken as well for security reasons. If performance problems have to upgrade hardware :)
     * 
     */
    public function generateToken()
    {

        //Validate email if it is valid email
        $email = $this->validateParameter('email', $this->param['email'], EMAIL);
        //Check password if it meet site requirements.
        $password = $this->validateParameter('password', $this->param['password'], PASSWORD);
        
        //moved from __construct, is this better way?
        $db = new Database();
        $this->database = $db->connect();
        
        $sql = $this->database->prepare("SELECT Id, Password, Disabled FROM users WHERE email = :email");
        $sql->bindParam(":email", $email);
        $sql->execute();
        $user = $sql->fetch(PDO::FETCH_ASSOC);
        
        // Check if the email matches in database
        if ( !is_array($user) ) {
            $this->response(INVALID_USER_PASS, "Email or Password is incorrect.");
        }
        
        // Deny login if user is disabled (disabled = 1)
        if ( $user['Disabled'] == 1 ) {
            $this->response(USER_IS_DISABLED, "User is not activated.");
        }
        
        // Finally let's check if password is valid.
        if ( !password_verify($password, $user['Password']) ) {
            $this->throwException(INVALID_USER_PASS, "Email or Password is incorrect.");
        }

        //if everything is ok lets call token generator

        $this->user->setUserId($user['Id']);
        $this->generateTokens($this->user->getUserId(), 0, true);
        
    }
    
    /**
     * 
     * 
     */
    public function generateCodes() 
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_CODE, PERM_DESCR_GENERATE_CODE) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to generate codes");
        }
        $this->validateParameter('ammount', $this->param['ammount'], INTEGER);
        
        if ( !$this->qr->generateQrCodes($this->user->getUserid(), $this->param['ammount']) ) {
            $this->throwException(QR_FAILED_GENERATE_CODES, "Failed to generate QR codes");
        }
        $this->response(QR_SUCCESS_CODE_GENERATED, "QR codes successfully generated");
    }
    
    
    public function getUsedCodes()
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_CODE, PERM_DESCR_LABEL_CODE) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to get used codes");
        }
        //$this->response(QR_SUCCESS_GET_USED_HASHES, $this->qr->getUsedHashes());
        if ( !($this->qr->getUsedHash($this->user->getUserId())) ) {
            $this->throwException(666, "jooo");
        } else {
            $this->response(QR_SUCCESS_GET_USED_HASHES, $this->qr->usedHash());
        }
        $this->response(SUCCESS_RESPONSE, "ok");
    }
    
    public function getUnusedCodes()
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_CODE, PERM_DESCR_LABEL_CODE) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to get unused codes");
        }
        //$this->response(QR_SUCCESS_GET_UNUSED_HASHES, $this->qr->getUsedHashes());
        if ( !($this->qr->getUnusedHash($this->user->getUserId())) ) {
            $this->throwException(666, "jooo");
        } else {
            $this->response(QR_SUCCESS_GET_UNUSED_HASHES, $this->qr->unusedHash());
        }
        $this->response(SUCCESS_RESPONSE, "ok");
    }
    
    public function clearUnusedCodes() 
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_CODE, PERM_DESCR_CLEAR_CODE) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to clear unused codes");
        }
        $this->validateParameter('disabled', $this->param['disabled'], INTEGER, false);
        
        if ( !$this->qr->deleteUnusedCodes($this->user->getUserId()) ) {
            $this->throwException(QR_FAILED_CLEAR_UNUSED, "Failed to clear unused hashes");
        }
        $this->response(QR_SUCCESS_CLEAR_UNUSED, "Successfully cleared unused hashes");
    }
    
    public function printUnusedCodes() 
    {
        
        $filename = $this->qr->createPdfFromUnusedCodes($this->user->getUserId());
        
        $this->response(QR_PDF_GENERATED, $filename);
    }
    
    public function eventFromHashId()
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_EVENT, PERM_DESCR_CREATE_EVENT) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to generate events");
        }
        $this->validateParameter('hashid', $this->param['hashid'], INTEGER);
       
        // This allows only current user to modify own hash. 
        if ( !$this->qr->makeEventFromHash($this->user->getUserId(), $this->param['hashid']) ) {
            $this->throwException(QR_FAILED_TO_MODIFY_TYPE, 'Failed to modify qr type');
        }
        $this->response(EVENT_SUCCESS_CREATED, 'Event successfully created');
    }
    
    public function getEventList()
    {
        // check if user has permission to get events
        if ( !$this->user->checkPrivilige(PERM_EVENT, PERM_DESCR_EDIT_EVENT) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to edit event");
        }
        //$this->response(QR_SUCCESS_GET_UNUSED_HASHES, $this->qr->getUsedHashes());
        if ( !($this->qr->getEvents($this->user->getUserId())) ) {
            $this->throwException(666, "jooo");
        } else {
            $this->response(EVENT_SUCCESS_GET_LIST, $this->qr->unusedHash());
        }
        
    }
    
    public function editEvent() 
    {
        // check if user has permission to edit users
        if ( !$this->user->checkPrivilige(PERM_EVENT, PERM_DESCR_EDIT_EVENT) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to edit event");
        }
        
        // validate input
        $this->validateParameter('id', $this->param['id'], INTEGER);
        $this->validateParameter('code', $this->param['code'], STRING);
        $this->validateParameter('name', $this->param['name'], STRING, false);
        $this->validateParameter('descr', $this->param['descr'], STRING, false);
        
        
        // if validation ok, let's proceed to update event information
        // first set current event properties from input
        $this->qr->setEventId($this->param['id']);
        $this->qr->setEventCode($this->param['code']);
        $this->qr->setEventDescr($this->param['descr']);
        $this->qr->setEventName($this->param['name']);

        // Handle update process response
        if ( !$this->qr->updateEvent($this->user->getUserId()) ) {
            $this->throwException(EVENT_FAILED_TO_EDIT, "Cannot edit event");
        }
        
        $this->response(EVENT_SUCCESS_EDIT, "Event successfully edited");
    }
    
    public function getEventCodes() 
    {
        // check if user has permission to edit users
        if ( !$this->user->checkPrivilige(PERM_EVENT, PERM_DESCR_EDIT_EVENT) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to edit event");
        }
        // validate input
        $this->validateParameter('id', $this->param['id'], INTEGER);
        // set parameter
        $this->qr->setEventId($this->param['id']);
        
        if ( !$this->qr->getEventCodesFromDb($this->user->getUserId()) ) {
            $this->throwException(EVENT_FAILED_TO_LIST_CODES, "Failed to list codes");
        }
        $this->response(EVENT_SUCCESS_LIST_CODES, $this->qr->usedHash());
    }
    
    /**
     * Test function.
     */
    public function testAuthorization()
    {
        echo ("Authorization ok.");
    }
    
    public function deleteUser() {
        // check if user has permission to add users
        if ( !$this->user->checkPrivilige(PERM_USER, PERM_DESCR_DELETE_USER) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to delete user");
        }

        $tempUser = new User();
        $tempUser->setUserId($this->param['userid']);

        if ( !$tempUser->delete() ) {
            $this->throwException(USER_IS_DISABLED, "User is no longer available or it's already deleted");
        }
        $tempUser = null;
        $this->response(SUCCESS_RESPONSE, "User deleted");
    }
    
    /**
     * addUser
     * 
     */
    public function addUser() 
    {
        // check if user has permission to add users
        if ( !$this->user->checkPrivilige(PERM_USER, PERM_DESCR_ADD_USER) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to add user");
        }
        // validate input

        $this->validateParameter('firstname', $this->param['firstname'], STRING);
        $this->validateParameter('lastname', $this->param['lastname'], STRING);
        $this->validateParameter('email', $this->param['email'], EMAIL);
        $this->validateParameter('email', $this->param['email'], EMAIL_DB);
        $setpassword = false;
        if ( !empty($this->param['password']) ) {
            if ( $this->param['password'] == $this->param['retypepassword']) {
                $this->validateParameter('password', $this->param['password'], PASSWORD);
                $setpassword = true;
            }
        } else {
            $this->throwException(PASSWORD_NOT_COMPLEX_ENOUGH, "Cannot add user without password");
        }
        
        $tempUser = new User();
        
        $tempUser->setFirstName($this->param['firstname']);
        $tempUser->setLastName($this->param['lastname']);
        $tempUser->setEmail($this->param['email']);
        
        if ( $setpassword ){
            $tempUser->addUser($this->param['password']);
        } else {
            // should never get here
            $this->throwException(666, "ERROR - cannot add user without password");
        }
        
        $this->response(SUCCESS_RESPONSE, $tempUser->setUserPermissions($this->param['permissions']));
        
    }
    
    public function editUser() 
    {
        // check if user has permission to edit users
        if ( !$this->user->checkPrivilige(PERM_USER, PERM_DESCR_EDIT_USER) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to update user permissions");
        }
        
        // validate input
        $this->validateParameter('userid', $this->param['userid'], INTEGER);
        $this->validateParameter('firstname', $this->param['firstname'], STRING);
        $this->validateParameter('lastname', $this->param['lastname'], STRING);
        $this->validateParameter('email', $this->param['email'], EMAIL);
        $setpassword = false;
        if ( !empty($this->param['password']) ) {
            if ( $this->param['password'] == $this->param['retypepassword']) {
                $this->validateParameter('password', $this->param['password'], PASSWORD);
                $setpassword = true; 
            }
        }
        
        
        // if validation ok, let's proceed to update user information 
        $tempUser = new User();
        $tempUser->setUserId($this->param['userid']);
        $tempUser->setFirstName($this->param['firstname']);
        $tempUser->setLastName($this->param['lastname']);
        $tempUser->setEmail($this->param['email']);
        
        if ( $setpassword ){
            $tempUser->updateBasicInformation($this->param['password']);
        } else {
            $tempUser->updateBasicInformation();
        }
        
        $this->response(SUCCESS_RESPONSE, $tempUser->setUserPermissions($this->param['permissions']));
        
    }
    
    /**
     * 
     */
    public function getUserPermById() 
    {
        // validate input
        $id = $this->validateParameter('userid', $this->param['userid'], INTEGER);
        $this->response(SUCCESS_RESPONSE, $this->user->getUserPerm($id));   
    }
    
    /**
     * getRoles
     * 
     * Gets user roles
     * 
     */
    public function getRoles() 
    {
        //echo("User id: " . $this->user->getUserId());
        //print_r($this->user->getRoles());
        $this->response(SUCCESS_RESPONSE, $this->user->getRoles());
    }
    
    /**
     * getPermissions
     * 
     * gets user permissions
     */
    public function getPermissions() 
    {
        $this->response(SUCCESS_RESPONSE, $this->user->getPermissions());
    }
    /**
     * getAllActiveUsers
     * 
     */
    public function getUsers() 
    {
        $this->response(SUCCESS_RESPONSE, $this->user->getUsers());
    }
    
    /** THESE DO NOT REQUIRE AUTHORIZATION 
     *  
     *  Write all API functions here which do no require authorization
     *  
     * **/
    
    /*
     * 
     */
    public function getGallery() 
    {
        // Parameter is required as a string
        $this->validateParameter('code', $this->param['code'], STRING);
        $this->qr->setEventCode($this->param['code']);
        
        if ( !$this->qr->getGallery() ) { 
            $this->throwException(GALLERY_NOT_AVAILABLE, "Nothing found with your code. Double check your code.");
        }

        $this->response(GALLERY_SUCCESS_OBTAINED, $this->qr->getImages());
    }
    
    
}

?>