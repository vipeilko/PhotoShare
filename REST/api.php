<?php
/**
 * 
 * @version 0.0.1
 * @author Ville Kouhia
 * Changelog
 * 15.2.2020 First implementation
 *
 */    

use \Firebase\JWT\JWT;

    class Api extends Rest {
        public $database;
        
        public function __construct() {
            parent::__construct();
            
            $db = new Database;
            $this->database = $db->connect();
        }
        
        public function generateToken() {
           // print_r($this->param);
           $email = $this->validateParameter('email', $this->param['email'], STRING);
           $password = $this->validateParameter('password', $this->param['password'], STRING);
           
           $sql = $this->database->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
           $sql->bindParam(":email", $email);
           $sql->bindParam(":password", $password);
           $sql->execute();
           $user = $sql->fetch(PDO::FETCH_ASSOC);
           
           print_r($user); //Debug
           
           //Check if the username and password matches in database
           if ( !is_array($user) ) {
               $this->response(INVALID_USER_PASS, "Email or Password is incorrect.");
               
           }
           
           //Deny login if user is disabled (disabled = 1)
           if ( $user['Disabled'] == 1 ) {
               $this->response(USER_IS_DISABLED, "User is not activated.");
               
           }
           
           $payload = [
               'iat' => time(),
               'iss' => 'localhost',
               'exp' => time() + (AGE_OF_TOKEN),
               'userId' => $user['Id']
           ];
           
           $token = JWT::encode($payload, API_KEY);
           
           //echo $token; //Debug
           $data = ['token' => $token];
           $this->response(SUCCESS_RESPONSE, $data);
           
        }
    
    }

?>