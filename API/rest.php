<?php
/**
 * Rest() 
 * 
 * @version 0.0.1
 * @author Ville Kouhia
 *
 * Changelog
 *  + 15.2.2020 First implementation
 *  + 16.2.2020 Cleaned code a bit and more comments
 *  + 17.2.2020 Added validateRefreshToken, still in process
 *              moved db connection introduction from __construct to function
 *  
 *
 */
use Firebase\JWT\JWT;



class Rest
{

    protected $request;
    protected $serviceName;
    protected $param;

    public function __construct()
    {
        
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            // echo "Method is not post";
            $this->throwException(REQUEST_METHOD_NOT_VALID, 'Request Method is not valid');
        }
        $input_stream = fopen('php://input', 'r');
        $this->request = stream_get_contents($input_stream);
        
        $this->validateRequest($this->request);
        
        // echo ("Service name: " . $this->serviceName . "\n"); //debug

        if ('generatetoken' != strtolower($this->serviceName)) {
            $this->validateAccessToken();
        } else if ('validaterefreshtoken' != strtolower($this->service)){
            $this->validateRefreshToken();
        }
        
    }

    /* validateRequest
     * Validates application/json content type requests. 
     * 
     * */
    public function validateRequest($request)
    { 

        if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
            $this->throwException(REQUEST_CONTENTTYPE_NOT_VALID, 'Requested content-type is not valid');
        }
        
        $data = json_decode($this->request, true);

        if (!isset($data['name']) or $data['name'] == "") {
            $this->throwException(API_NAME_REQUIRED, "API name required.");
        }
        $this->serviceName = $data['name'];

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
     * Very basic data validation for boolean, integer and string
     * 
     * @param $fieldName            // fieldName email for example
     * @param $value                // input value
     * @param $dataType             // data type boolean, integer, string
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
     * validateRefreshToken
     * 
     * validates refresh token if it is still good to use
     * 
     */
    public function validateRefreshToken()
    {
        try {
            echo  $this->userId;
            
        } catch (Exception $e) {
            $this->throwException(REFRESH_TOKEN_ERROR, $e->getMessage());
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
            
            //moved SQL connection from __construct(). At least do not make so many connections to database. 
            $db = new Database();
            $this->database = $db->connect();
            // Try to get token from header
            $token = $this->getBearerToken();
            $payload = JWT::decode($token, API_ACCESS_TOKEN_KEY, [''.API_ALGORITHM.'']);
            
            $sql = $this->database->prepare("SELECT * FROM users WHERE Id = :id");
            $sql->bindParam(":id", $payload->userId);
            $sql->execute();
            $user = $sql->fetch(PDO::FETCH_ASSOC);
            if (!is_array($user)) {
                $this->response(INVALID_USER_PASS, "This user is not found in our database");
            }
            
            if ($user['Disabled'] == 1 ) {
                $this->response(USER_IS_DISABLED, "This user is disabled.");
            }
            
            $this->userId = $payload->userId;
            
        } catch (Exception $e) {
            $this->throwException(ACCESS_TOKEN_ERROR, $e->getMessage());
        }
        
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
        header("content-type: application/json");
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
        header("content-type: application/json");
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
     * 
     * @param $code
     * @param $data
     */
    
    public function warning($code, $data) 
    {
        header("content-type: application/json");
        $response = json_encode([
            'response' => [
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
        
        echo ("Headers: " . $headers ."\n");
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
                echo ("Token: " . $token[1] ."\n");
                return $token[1];
            } 
            $this->throwException(COULD_NOT_GET_AUTHORIZATION_FROM_HEADER, 'Could not parse access token from authorization header');    
        }
    }
    
}

?>