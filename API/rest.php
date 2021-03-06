<?php
/**
 * Rest() 
 * 
 * @version 1.0
 * @author Ville Kouhia
 *
 * Changelog
 *  + 15.02.2020 First implementation
 *  + 16.02.2020 Cleaned code a bit and more comments
 *  + 17.02.2020 Added validateRefreshToken, still in process
 *               moved db connection introduction from __construct to function
 *  + 18.02.2020 Continued refreshtoken handling
 *  + 19.02.2020 RefreshToken db connections and handling
 *  + 20.02.2020 Finnished refreshToken. User can't renew tokens forever anymore with refreshToken. 
 *               From first request of tokens user needs to pass credentials again in AGE_OF_REFRESH_TOKEN 
 *               seconds. Default 86 400 seconds or 1 day. More specific throw exceptions on database connections.
 *  + 27.03.2020 A small update to responses between login ok 200 and refreshToken uptate. Makes it easier to handle on client side
 *               asyncrounous ajax calls.
 *  + 02.04.2020 User class is now declared here for software wide use. Added user related functions.
 *  + 12.04.2020 Add and edit user
 *  + 24.04.2020 get/generate/clear/print codes
 *  + 05.06.2020 Fist release version 1.0
 *  
 */
use Firebase\JWT\JWT;



class Rest
{
    protected $request;         // contains http post inputstream
    protected $serviceName;     // contains requested service name
    protected $param;           // contains parameters in request
    
    protected user $user;       // contains current user 
    
    /**
     * Constructor defines operations that requested service needs
     * If service requires authorization it is needed to be defined here
     * All services which is added must be added to this constructor as well, 
     * otherwise service is not available
     * 
     * 
     */
    public function __construct()
    {
        
        // API accepts only POST request for now
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // echo "Method is not post";
            $this->throwException(REQUEST_METHOD_NOT_VALID, 'Request Method is not valid');
        }
        // open input stream from HTTP POST
        $input_stream = fopen('php://input', 'r');
        $this->request = stream_get_contents($input_stream);
        
        $this->validateRequest($this->request);
        
        // generate user object
        $this->user = new user();
        
        
        // Switch-case to handle different type request
        // Probalby best practice is list all cases here, so not a single service(function) is available without intend
        switch ($this->serviceName) 
        {

            case "validateAccessToken":
                $this->validateAccessToken();
                break;
            case "validateRefreshToken":
                $this->validateRefreshToken();
                break;
            case "generateToken":
                //
                break;
            case "getUserPermById":
                $this->validateAccessToken();
                $this->getUserPermById();
                break;
            // list all authorization needed services here
            case "startProcessingImages":
            case "getEventCodes":
            case "editEvent":
            case "getEventList":
            case "eventFromHashId":
            case "printUnusedCodes":
            case "clearUnusedCodes":
            case "generateCodes":
            case "getUnusedCodes":
            case "getUsedCodes":
            case "deleteUser":
            case "addUser":
            case "editUser":
            case "getUsers":
            case "getPermissions":
            case "getRoles":
            case "testAuthorization":
                $this->validateAccessToken();
                break;
            // These request do not require authorization
            // Carefully add functions here
            case "getGallery":
            case "isGalleryAvailable":
                break;
            default:
                //If service is allready implemented but not list above we get this error
                $this->throwException(API_DOES_NOT_EXIST, "API does not exist.");
        }
        
    }

    /* validateRequest
     * Validates application/json content type requests. 
     * 
     * */
    public function validateRequest($request)
    {
        // only JSON content is accepted
        if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
            $this->throwException(REQUEST_CONTENTTYPE_NOT_VALID, 'Requested content-type is not valid');
        }
        // decode json data
        $data = json_decode($this->request, true);
        //print_r($data);
        
        // if post serviceName is not set or empty throws exception 
        if (!isset($data['serviceName']) or $data['serviceName'] == "") {
            $this->throwException(API_NAME_REQUIRED, "API name required.");
        }
        // otherwise save requested serviceName
        $this->serviceName = $data['serviceName'];
        
        // parameters need to be an array
        if (!is_array($data['param'])) {
            $this->throwException(API_PARAM_REQUIRED, "API PARAM required.");
        }
        $this->param = $data['param'];
        // print_r($data);
    }

    /**
     * processApi
     * 
     * Main API function verifies that method that client is asking is available.
     * For example generateToken 
     * 
     */
    public function processApi()
    {
        try {
            $api = new API();
            $rMethod = new reflectionMethod('API', $this->serviceName);
            if (!method_exists($api, $this->serviceName)) {
                $this->throwException(API_DOES_NOT_EXIST, "API does not exist.");
            }
            $rMethod->invoke($api);
        } catch (Exception $e) {
            // Not valid service
            $this->throwException(API_DOES_NOT_EXIST, $e->getMessage());
        }
    }

    /**
     * validateParameter
     * 
     * Data validation function
     * 
     * @param $fieldName            // fieldName email for example
     * @param $value                // input value
     * @param $dataType             // PASSWORD, EMAIL, BOOLEAN, INTEGER, STRING
     * @param boolean $required     // is data required
     * @return $value               // returns validated data
     */
    
    public function validateParameter($fieldName, $value, $dataType, $required = true)
    {
        //if data is required throws exception
        if ($required == true and empty($value) == true) {
            $this->throwException(VALIDATE_PARAMETER_REQUIRED, $fieldName . " parameter is required");
        }

        switch ($dataType) {
            case PASSWORD:
                // min lenght is defined in settings.php
                if ( strlen($value) < PASSWORD_MIN_LENGTH ) {
                    $this->throwException(PASSWORD_NOT_COMPLEX_ENOUGH, "Password too short!");
                }
                if ( !preg_match("#[0-9]+#", $value) ) {
                    $this->throwException(PASSWORD_NOT_COMPLEX_ENOUGH, "Password not meeting complexity requirements!");
                }
                if ( !preg_match("#[a-z]+#", $value) ) {
                    $this->throwException(PASSWORD_NOT_COMPLEX_ENOUGH, "Password not meeting complexity requirements!");
                }
                if ( !preg_match("#[A-Z]+#", $value) ) {
                    $this->throwException(PASSWORD_NOT_COMPLEX_ENOUGH, "Password not meeting complexity requirements!");
                }
                if ( !preg_match("#\W+#", $value) ) {
                    $this->throwException(PASSWORD_NOT_COMPLEX_ENOUGH, "Password not meeting complexity requirements!");
                }
                break;
            case EMAIL:
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->throwException(VALIDATE_PARAMETER_EMAIL, "Invalid email format for " . $fieldName . "");
                }
                break;
            case EMAIL_DB:
                if ($this->user->checkEmail($value)) {
                    $this->throwException(VALIDATE_PARAMETER_EMAIL, "Email is already in use");
                }
                break;
            case BOOLEAN:
                if (is_bool($value)) {
                    $this->throwException(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for " . $fieldName . ". It should be boolean.");
                }
                break;
            case INTEGER:
                if (!is_numeric($value)) {
                    $this->throwException(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for " . $fieldName . ". It should be numeric.");
                }
                break;
            case STRING:
                if (!is_string($value)) {
                    $this->throwException(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for " . $fieldName . ". It should be string.");
                }
                break;
            default:
                $this->throwException(VALIDATE_PARAMETER_REQUIRED, "Datatype is not valid for " . $fieldName);
                break;
        }

        return $value;
    }
        
    /**
     * generateTokens
     * 
     * @param int $userid
     * @param int $refreshTokenExp
     * 
     * Generates always both tokens for $userid. If refreshtokenexp is anything else than 0 it will use it as experiation date of refreshtoken.
     * Otherwise refreshToken is valid for one day by default. Configurable from settings.php.
     * 
     * 
     */
    public function generateTokens($userid, $refreshTokenExp, $login = false) 
    {
        //echo"USERID :" . $userid ."\n"; // debug
        $accessTokenPayload = [
            'iat' => time(),                                    // time of issue
            'iss' => gethostname().'/'.$_SERVER['SERVER_ADDR'], // gets hostname and ip address to issuer
            'exp' => time() + (AGE_OF_ACCESS_TOKEN),            // token experiation time
            'userId' => $userid                                 // userid to include in token
        ];
        // if refreshtokenexp is zero do basic payload
        if ( $refreshTokenExp == 0 ) {
            $refreshTokenPayload = [
                'iat' => time(),                                    // time of issue
                'iss' => gethostname().'/'.$_SERVER['SERVER_ADDR'], // gets hostname and ip address to issuer
                'exp' => time() + (AGE_OF_REFRESH_TOKEN),           // token experiation time
                'userId' => $userid                                 // userid to include in token
            ];
        } 
        // else use orginal refreshtoken experiation time.
        else { 
            $refreshTokenPayload = [
                'iat' => time(),                                    // time of issue
                'iss' => gethostname().'/'.$_SERVER['SERVER_ADDR'], // gets hostname and ip address to issuer
                'exp' => $refreshTokenExp,                          // token experiation time
                'userId' => $userid                                 // userid to include in token
            ];
        }
        
        try {
            //Generate both tokens
            $accessToken = JWT::encode($accessTokenPayload, API_ACCESS_TOKEN_KEY, API_ALGORITHM);
            $refreshToken = JWT::encode($refreshTokenPayload, API_REFRESH_TOKEN_KEY, API_ALGORITHM);
            
        } catch (Exception $e) {
            $this->throwException(JWT_PROCESSING_ERROR, $e->getMessage());
        }
        
        //echo 'Database connection status after query: ' . $this->database->getAttribute(PDO::ATTR_CONNECTION_STATUS) ."\n"; // For debugg
        // DB UPDATE OF ACCESSTOKEN STARTS FROM HERE
        $tokenType = TOKEN_TYPE_ACCESS;
        
            $sql = null;
            $stmt = null;
            $count = null;
        try {
            $sql = ("SELECT UserId FROM tokens WHERE
                                        UserId = :UserId AND
                                        TokenType = :TokenType");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':UserId',       $userid,     PDO::PARAM_STR);
            $stmt->bindParam(':TokenType',    $tokenType,  PDO::PARAM_STR);
            
            $stmt->execute();
            $count = $stmt->rowCount();
            //echo("rowCount: " . $count . "\n"); //debug
            
            if ( $count > 1 ) {
                $this->throwException(TOO_MANY_TOKENS, "Too many tokens. Hacking?");
            }
        } catch (Exception $e) {
            $this->throwException(DATABASE_ERROR, "Database select error.");
        }
        if ( $count == 1 ) {
            // if only one found, lets update it
            $sql = null;
            $stmt = null;
            try {
                $sql = ("UPDATE tokens SET
                                Token = :newToken WHERE
                                UserId = :UserId AND
                                TokenType = :TokenType");
                $stmt = $this->database->prepare($sql);
                $stmt->bindParam(':UserId',       $userid,        PDO::PARAM_STR);
                $stmt->bindParam(':TokenType',    $tokenType,     PDO::PARAM_STR);
                $stmt->bindParam(':newToken',     $accessToken,   PDO::PARAM_STR);
                
                $stmt->execute();
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database update error.");
            }
            
        } 

        if ( $count == 0) {
            //echo "RefreshTokenPayload exp: " . $refreshTokenPayload['exp'];
            //else it is zero and we insert it
            try {
                $sql = ("INSERT INTO tokens(UserId,
                                            TokenType,
                                            Token) VALUES (
                                            :UserId,
                                            :TokenType,
                                            :Token)");
                
                $stmt = $this->database->prepare($sql);
                $stmt->bindParam(':UserId',       $userid,                      PDO::PARAM_STR);
                $stmt->bindParam(':TokenType',    $tokenType,                   PDO::PARAM_STR);
                $stmt->bindParam(':Token',        $refreshToken,                PDO::PARAM_STR);
        
                $stmt->execute();
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database insert error.");
            }
        }
        // DB UPDATE OF ACCESSTOKEN ENDS HERE
        // DB UPDATE OF REFRESHTOKEN STARTS FROM HERE
        $tokenType = TOKEN_TYPE_REFRESH;
        $sql = null;
        $stmt = null;
        try {
            $sql = ("SELECT UserId FROM tokens WHERE 
                                    UserId = :UserId AND 
                                    TokenType = :TokenType");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':UserId',       $userid,     PDO::PARAM_STR);
            $stmt->bindParam(':TokenType',    $tokenType,  PDO::PARAM_STR);
            
            $stmt->execute();
            $count = $stmt->rowCount();
        } catch (Exception $e) {
            $this->throwException(DATABASE_ERROR, "Could not fetch data from database.");
        }
        //echo("rowCount: " . $count . "\n");
        
        if ( $count > 1 ) {
            $this->throwException(TOO_MANY_TOKENS, "Too many tokens. Hacking?");
        } 
        
        if ( $count == 1 ) {
            // if only one found, lets update it
            $sql = null;
            $stmt = null;
            try {
                $sql = ("UPDATE tokens SET 
                            Token = :newToken WHERE 
                            UserId = :UserId AND 
                            TokenType = :TokenType");
                $stmt = $this->database->prepare($sql);
                $stmt->bindParam(':UserId',       $userid,        PDO::PARAM_STR);
                $stmt->bindParam(':TokenType',    $tokenType,     PDO::PARAM_STR);
                $stmt->bindParam(':newToken',     $refreshToken,  PDO::PARAM_STR);
                
                $stmt->execute(); 
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database update error.");
            }
        } 
        if ( $count == 0 ){ 
            //else it is zero and we insert it 
            try {
                $sql = ("INSERT INTO tokens(UserId,
                                            TokenType,
                                            Token) VALUES (
                                            :UserId,
                                            :TokenType,
                                            :Token)");
                
                $stmt = $this->database->prepare($sql);
                $stmt->bindParam(':UserId',       $userid,                      PDO::PARAM_STR);
                $stmt->bindParam(':TokenType',    $tokenType,                   PDO::PARAM_STR);
                $stmt->bindParam(':Token',        $refreshToken,                PDO::PARAM_STR);
                
                $stmt->execute();
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database insert error.");
            }
        }
        // DB UPDATE OF REFRESHTOKEN ENDS HERE
        // print_r($data); //Debug
        
        //After succesfully updating both tokens will return JSON to client
        $data = [
            'accessToken'   => $accessToken,
            'refreshToken'  => $refreshToken
        ];
        
        //close db connection
       // $this->database->disconnect();
       
        if ( $login ) {
            //if from login / success 200
            $this->response(SUCCESS_RESPONSE, $data);
        } else {
            // refreshtoken update response
            $this->response(SUCCESS_UPDATE_REFRESH_TOKEN, $data);
        }
    }
    
    /**
     * validateRefreshToken
     * 
     * validates refresh token if it is still good to use
     * 
     */
    public function validateRefreshToken()
    {
        try {

            // Try to get token from header
            $token = $this->getBearerToken();
            
            // let's check if refreshToken is valid
            $refreshToken = JWT::decode($token, API_REFRESH_TOKEN_KEY, [''.API_ALGORITHM.'']);
           
            //print_r($refreshToken);
            $db = new Database();
            $this->database = $db->connect();            
            
            $tokenType = TOKEN_TYPE_REFRESH;
            
            try {
                $sql = $this->database->prepare("SELECT UserId, 
                                                        Token FROM 
                                                        tokens WHERE 
                                                        Token = :token AND 
                                                        TokenType = :tokentype");
                $sql->bindParam(":token", $token);
                $sql->bindParam(":tokentype", $tokenType);
                $sql->execute();
                $dbToken = $sql->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database select error.");
            }
            
            //print_r($tokens);
            
            // if there is no any results from db return that there is no matchign refresh token
            if (!is_array($dbToken)) {
                $this->response(NO_MATCHING_REFRESH_TOKEN, "No matching refresh token found.");    
            }
            
            // Not needed, JWT::decode should throw an exception before this
            if ($refreshToken->exp < time()) {
                $this->throwException(REFRESH_TOKEN_ERROR, "dsadsa");
            }
            
            //print_r($dbToken);//debug
            
            //if validation has passed all above we still have to check if user is not disabled
            $sql = null;
            try {
                $sql = $this->database->prepare("SELECT Id, Disabled FROM users WHERE Id = :id");
                $sql->bindParam(":id", $dbToken['UserId']);
                $sql->execute();
                $user = $sql->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database select error.");
            }
            
            if ($user['Disabled'] == 1 ) {
                $this->response(USER_IS_DISABLED, "This user is disabled.");
            }
            
            // if refreshToken is valid. Let's generate new Tokens. Both tokens are always generated, 
            // so it is enough to call generateTokens
            $this->generateTokens($dbToken['UserId'], $refreshToken->exp);
            
        } catch (Exception $e) {
            //close db connection
           //$this->database->disconnect();
            $this->throwException(REFRESH_TOKEN_ERROR, "RefreshToken: ".$e->getMessage());
        }
    }
    
    /**
     * validateAccessToken
     * 
     * validates access token if it is still good to use 
     */
    public function validateAccessToken()
    {
        try {
            
            // Try to get token from header
            $token = $this->getBearerToken();
            //echo("TOKEN: " .$token."\n"); //debug
            $payload = JWT::decode($token, API_ACCESS_TOKEN_KEY, [''.API_ALGORITHM.'']);
            
            //moved SQL connection from __construct(). At least do not make so many connections to database. 
            try {
                $db = new Database();
                $this->database = $db->connect();
                $sql = $this->database->prepare("SELECT Id, Disabled FROM users WHERE Id = :id");
                $sql->bindParam(":id", $payload->userId);
                $sql->execute();
                $user = $sql->fetch(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database select error.");
            }
            if (!is_array($user)) {
                $this->response(INVALID_USER_PASS, "This user is not found in our database");
            }
            
            if ($user['Disabled'] == 1 ) {
                $this->response(USER_IS_DISABLED, "This user is disabled.");
            }
            
            // if validated correctly lets set 
            
            $this->user->setUserId($payload->userId);
            //$this->userId = $payload->userId;
            
        } catch (Exception $e) {
            //close db connection
            //$this->database->disconnect();
            $this->throwException(ACCESS_TOKEN_ERROR, "AccessToken: ".$e->getMessage());
        }
        //close db connection
        //$this->database->disconnect();
        
    }
    
    /**
     * throwExecption 
     * 
     * Throws general exceptions, returns application/json, status code and message then quits the program. 
     * Exception codes are defined in settings.php 
     * 
     * @param $code
     * @param $message
     * 
     */
    public function throwException($code, $message)
    {
        header("content-type: application/json"); // ; charset=utf-8
        $error_message = json_encode([
            'error' => [
                'status' => $code,
                'message' => $message
            ]
        ]);
        echo $error_message;
        exit();
    }

    /**
     * response
     * 
     * returns response for request, for example access token
     * 
     * @param $code
     * @param $data
     */
    public function response($code, $data)
    {
        header("content-type: application/json; charset=utf-8");
        $response = json_encode([
            'response' => [
                'status' => $code,
                'message' => $data
            ]
        ]);
        echo $response;
        exit();
    }
    
    /**
     * warning 
     * 
     * TODO: warns user that some settings are in "developer mode" for example access token life is too long
     *       database passwords, ect...
     *       Not implemented yet in version 1.0
     * 
     * @param $code
     * @param $data
     */
    public function warning($code, $data) 
    {
        header("content-type: application/json");
        $response = json_encode([
            'warning' => [
                'status' => $code,
                'message' => $data
            ]
        ]);
        echo $response;
       // exit(); // do not have to exit
    }
    
    /**
     * getAuthorizationHeader
     * TODO: Probalby should use zend engine or similar to handle better getting right information from headers. 
     *       
     * Gets and Parses authorization header
     * 
     */
    public function getAuthorizationHeader() 
    {
        $headers = null;
        
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER['Authorization']);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
        } elseif (function_exists('apache_request_headers')) {
            $apacheHeaders = apache_request_headers();
            
            $apacheHeaders = array_combine(array_map('ucwords', array_keys($apacheHeaders)), array_values($apacheHeaders));
            if (isset($apacheHeaders['Authorization'])) {
                $headers = trim($apacheHeaders['Authorization']);
            }
        }
        
         //echo ("Headers: " . $headers ."\n"); //debug
         return $headers;
    }
    
    /**
     * getBearerToken
     *
     * Reads header and parses token from it and returns it or throws an exception 
     * 
     */
    public function getBearerToken() 
    {
        $headers = $this->getAuthorizationHeader();        
        
        if (!empty($headers)) {
            $token = explode(" ", $headers, 2);
            if (!empty($token[1])) {
                //echo ("Token: " . $token[1] ."\n"); //debug
                return $token[1];
            } 
            $this->throwException(COULD_NOT_GET_AUTHORIZATION_FROM_HEADER, 'Could not parse access token from authorization header');    
        }
    }
    
}

?>