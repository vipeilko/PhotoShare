<?php
/**
 * 
 * @version 0.0.1
 * @author Ville Kouhia
 * 
 * Changelog
 *  + 15.2.2020 First implementation
 *  + 16.2.2020 Cleaned code a bit and more comments
 *  + 17.2.2020 Added refresh token generation in generateToken
 *              Have to decide if both tokens are generated at same time. And if not, 
 *              will we start new period of AGE_OF_REFRESH_TOKEN or should we get original time 
 *              from database.
 *              Moved db connection introduction from __construct to function.
 *  + 24.2.2020 Added password hashing using built-in password_hash function
 */


class Api extends Rest
{

    public $database;

    public function __construct()
    {
        parent::__construct();

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
        if (! is_array($user)) {
            $this->response(INVALID_USER_PASS, "Email or Password is incorrect.");
        }
        
        // Deny login if user is disabled (disabled = 1)
        if ($user['Disabled'] == 1) {
            $this->response(USER_IS_DISABLED, "User is not activated.");
        }
        
        // Finally let's check if password is valid.
        if (!password_verify($password, $user['Password'])) {
            $this->throwException(INVALID_USER_PASS, "Email or Password is incorrect.");
        }

        //if everything is ok lets call token generator
        $this->generateTokens($user['Id'], 0);
        
    }
    /**
     * 
     * 
     */
    public function generateQrCodes() {
       
        // return generated qrcodes in json and links to images
    }
    /**
     * Test function.
     */
    public function testAuthorization()
    {
        echo ("Authorization ok.");
    }
    
    
}

?>