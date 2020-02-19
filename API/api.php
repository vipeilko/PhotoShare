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
 *  
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
        //moved from __construct, is this better way?
        $db = new Database();
        $this->database = $db->connect();
        
        // print_r($this->param);
        $email = $this->validateParameter('email', $this->param['email'], STRING);
        $password = $this->validateParameter('password', $this->param['password'], STRING);

        
            $sql = $this->database->prepare("SELECT Id, Disabled FROM users WHERE email = :email AND password = :password");
            $sql->bindParam(":email", $email);
            $sql->bindParam(":password", $password);
            $sql->execute();
            $user = $sql->fetch(PDO::FETCH_ASSOC);

            // print_r($user); // Debug

            // Check if the username and password matches in database
            if (! is_array($user)) {
                $this->response(INVALID_USER_PASS, "Email or Password is incorrect.");
            }

            // Deny login if user is disabled (disabled = 1)
            if ($user['Disabled'] == 1) {
                $this->response(USER_IS_DISABLED, "User is not activated.");
            }

            //if everything is ok lets call generator
            $this->generateTokens($user['Id']);

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