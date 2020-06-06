<?php
/**
 * 
 * @version 1.0
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
 *  + 05.06.2020 First release version 1.0
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
     * This verifyes email / password combination. 
     * Real token generation is done in rest.php
     * This is only called from login screen.
     * 
     */
    public function generateToken()
    {

        //Validate email if it is valid email
        $email = $this->validateParameter('email', $this->param['email'], EMAIL);
        //Check password if it meet site requirements.
        $password = $this->validateParameter('password', $this->param['password'], PASSWORD);
        
        // Get email matching information from db
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            $sql = $this->database->prepare("SELECT Id, Password, Disabled FROM users WHERE email = :email");
            $sql->bindParam(":email", $email);
            $sql->execute();
            $user = $sql->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->throwException(DATABASE_ERROR, "Database select error.");
        }
        
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
     * generateCodes
     * 
     * Verify input data
     * Verify user privileges
     * Pass input data to real qr-code generator in qr.php 
     * Return response success / failed
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
    
    
    /**
     * getUsedCodes
     * 
     * Verify input data
     * Verify user privileges
     * Get current users codes
     * Return response success[data] / throwException Failed
     * 
     */   
    public function getUsedCodes()
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_CODE, PERM_DESCR_GENERATE_CODE) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to get used codes");
        }
        //$this->response(QR_SUCCESS_GET_USED_HASHES, $this->qr->getUsedHashes());
        if ( !($this->qr->getUsedHash($this->user->getUserId())) ) {
            $this->throwException(QR_FAILED_TO_GET_CODES, "Failed to get codes");
        } else {
            $this->response(QR_SUCCESS_GET_USED_HASHES, $this->qr->usedHash());
        }
    }
    
    /**
     * getUnusedCodes
     *
     * Verify input data
     * Verify user privileges
     * Get current users codes
     * Return response success[data] / throwException Failed
     *
     */
    public function getUnusedCodes()
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_CODE, PERM_DESCR_GENERATE_CODE) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to get unused codes");
        }
        //$this->response(QR_SUCCESS_GET_UNUSED_HASHES, $this->qr->getUsedHashes());
        if ( !($this->qr->getUnusedHash($this->user->getUserId())) ) {
            $this->throwException(QR_FAILED_TO_GET_CODES, "Failed to get codes");
        } else {
            $this->response(QR_SUCCESS_GET_UNUSED_HASHES, $this->qr->unusedHash());
        }
    }
    
    /**
     * clearUnusedCodes
     *
     * Verify input data
     * Verify user privileges
     * clear current users codes
     * Return response success / throwException Failed
     *
     */
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
    
    /**
     * printUnusedCodes
     *
     * Passes information to generate PDF-file which contains all unused QR-codes
     * Return response success[path to file]
     *
     */
    public function printUnusedCodes() 
    {
        
        $filename = $this->qr->createPdfFromUnusedCodes($this->user->getUserId());
        
        $this->response(QR_PDF_GENERATED, $filename);
    }
    
    /**
     * eventFromHashId
     *
     * Verify input data
     * Verify user privileges
     * Makes event from a hash
     * Return response success / throwException Failed
     *
     */
    public function eventFromHashId()
    {
        // check if user has permission to get codes
        if ( !$this->user->checkPrivilige(PERM_EVENT, PERM_DESCR_CREATE_EVENT) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to generate events");
        }
        $this->validateParameter('hashid', $this->param['hashid'], INTEGER);
       
        // This allows only current user to modify own hash. 
        if ( !$this->qr->makeEventFromHash($this->user->getUserId(), $this->param['hashid']) ) {
            $this->throwException(QR_FAILED_TO_MODIFY_TYPE, 'Failed to modify code type event');
        }
        $this->response(EVENT_SUCCESS_CREATED, 'Event successfully created');
    }
    
    /**
     * getEventList
     *
     * Verify input data
     * Verify user privileges
     * Lists current user events 
     * Return response success[list] / throwException Failed
     *
     */
    public function getEventList()
    {
        // check if user has permission to get events
        if ( !$this->user->checkPrivilige(PERM_EVENT, PERM_DESCR_EDIT_EVENT) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to edit event");
        }
        //$this->response(QR_SUCCESS_GET_UNUSED_HASHES, $this->qr->getUsedHashes());
        if ( !($this->qr->getEvents($this->user->getUserId())) ) {
            $this->throwException(EVENT_FAILED_TO_LIST_CODES, "Unable to obtain eventlist");
        } else {
            $this->response(EVENT_SUCCESS_GET_LIST, $this->qr->unusedHash());
        }
        
    }
    
    /**
     * editEvent
     *
     * Verify input data
     * Verify user privileges
     * Updates event information, name and description
     * Return response success / throwException Failed
     *
     */
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
    
    /**
     * getEventCodes
     *
     * Verify input data
     * Verify user privileges
     * Gets all codes that is tied to an event
     * Return response success[list] / throwException Failed
     *
     */
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
    
    /**
     * deleteUser
     *
     * Verify input data
     * Verify user privileges
     * Do not really delete db record, but marks it as disabled
     * Return response success / throwException Failed
     *
     */
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
     * Verify input data
     * Verify user privileges
     * Updates user which is feeded with parameter information
     * Return response success / throwException Failed
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
        
        if ( $setpassword ) {
            $tempUser->addUser($this->param['password']);
        } else {
            // should never get here
            $this->throwException(666, "ERROR - cannot add user without password");
        }
        
        // add last used hash setting to new user default settings is 0
        if ( !$this->qr->insertLastUsedHash($tempUser->getUserId(), 0) ) {
            // failed
            // not too bad if fails, but processing codes might not succseed  
        }
        
        $this->response(SUCCESS_RESPONSE, $tempUser->setUserPermissions($this->param['permissions']));
        
    }
    
    /**
     * editUser
     *
     * Verify input data
     * Verify user privileges
     * Edit user which is feeded with parameter information
     * Return response success / throwException Failed
     *
     */
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
     * getUserPermById
     *
     * Verify input data
     * Gets user permissions
     * Return response success[list] / throwException Failed
     *
     */
    public function getUserPermById() 
    {
        // validate input
        if ( $this->param['userid'] != 0) {
            $id = $this->validateParameter('userid', $this->param['userid'], INTEGER);
        } else {
            $id = $this->param['userid'];
        }
        
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
    
    /**
     * startProcessingImages
     * 
     * Verify input data
     * Verify user privileges
     * startProcessingImages; resize, read code from image and link to events
     * Return response success[how many images was processed] / throwException Failed
     * 
     */
    public function startProcessingImages() 
    {
        // if code is null. Then normal processing and no validation
        if ( !($this->param['code'] == null) ) {

            // Parameter is required as a string
            $this->validateParameter('code', $this->param['code'], STRING);
            $this->qr->setEventCode($this->param['code']);
        }
        
        // check if user has permission to process codes
        if ( !$this->user->checkPrivilige(PERM_CODE, PERM_DESCR_PROCESS_CODE) ) {
            $this->throwException(USER_HAS_NO_RIGHT, "User has no right to process codes");
        }
        
        //$this->response(QR_SUCCESS_GET_UNUSED_HASHES, $this->qr->getUsedHashes());
        
        if ( !($this->param['code'] == null) ) {
            $ans = $this->qr->processImages($this->user->getUserId());
        } else {
            $ans = $this->qr->processImages($this->user->getUserId());
        }
        // returns 220 so client knows to ask more
        $this->response(QR_SUCCESS_PROCESS_IMAGES, $ans);
        
    }
    
    /** THESE DO NOT REQUIRE AUTHORIZATION 
     *  
     *  Write all API functions here which do no require authorization
     *  
     * **/
    
    /**
     * isGalleryAvailable
     * 
     * Checks if gallery with specific code is available
     * 
     */
    public function isGalleryAvailable() 
    {
        // Parameter is required as a string
        $this->validateParameter('code', $this->param['code'], STRING);
        $this->qr->setEventCode($this->param['code']);
        
        if ( !$this->qr->checkGalleryAvailability() ) {
            $this->throwException(GALLERY_NOT_AVAILABLE, "Nothing found with your code. Double check your code.");
        }
        // this gallery is available
        $this->response(GALLERY_AVAILABLE, $this->param['code']);
    }
    
    /**
     * getGallery
     * 
     * if gallery is found return response success[list] else nothing found / gallery not available
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