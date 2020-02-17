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
use Firebase\JWT\JWT;

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

        try {
            $sql = $this->database->prepare("SELECT * FROM users WHERE email = :email AND password = :password");
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

            $accessTokenPayload = [
                'iat' => time(),                                    // time of issue
                'iss' => gethostname().'/'.$_SERVER['SERVER_ADDR'], // gets hostname and ip address to issuer
                'exp' => time() + (AGE_OF_ACCESS_TOKEN),            // token experiation time
                'userId' => $user['Id']                             // userid to include in token
            ];

            $refreshTokenPayload = [
                'iat' => time(),                                    // time of issue
                'iss' => gethostname().'/'.$_SERVER['SERVER_ADDR'], // gets hostname and ip address to issuer
                'exp' => time() + (AGE_OF_REFRESH_TOKEN),           // token experiation time
                'userId' => $user['Id']                             // userid to include in token
            ];
            
            //Generate both tokens
            $accessToken = JWT::encode($accessTokenPayload, API_ACCESS_TOKEN_KEY, API_ALGORITHM);
            $refreshToken = JWT::encode($refreshTokenPayload, API_REFRESH_TOKEN_KEY, API_ALGORITHM);

            //TODO: update or insert refresh token to database. Now this only inserts new one, but never updates. 
            //      If kept this way, will be more easier to follow if someone is trying to obtain multiple refrestokens
            
            echo 'Database connection status after query: ' . $this->database->getAttribute(PDO::ATTR_CONNECTION_STATUS) ."\n"; // For debugg
            
            $sql = null;
            $tokenType = TOKEN_TYPE_REFRESH;
            
            $sql = ("INSERT INTO tokens(UserId, 
                                        TokenType,
                                        Token,
                                        Issued) VALUES (
                                        :UserId,
                                        :TokenType,
                                        :Token,
                                        :Issued)");
            
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(':UserId',       $user['Id'],                  PDO::PARAM_STR);
            $stmt->bindParam(':TokenType',    $tokenType,                   PDO::PARAM_STR);
            $stmt->bindParam(':Token',        $refreshToken,                PDO::PARAM_STR);
            $stmt->bindParam(':Issued',       $refreshTokenPayload['exp'],  PDO::PARAM_STR);
            
            $stmt->execute();
            
            // print_r($data); //Debug
            $data = [
                'accessToken'   => $accessToken,
                'refreshToken'  => $refreshToken
            ];
            
            $this->response(SUCCESS_RESPONSE, $data);
        } catch (Exception $e) {
            $this->throwException(JWT_PROCESSING_ERROR, $e->getMessage());
        }
    }
}

?>