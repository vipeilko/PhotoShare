<?php
/**
 * Rest() 
 * 
 * @version 0.0.1
 * @author Ville Kouhia
 *
 * Changelog
 * 15.2.2020 First implementation
 *
 */
require_once 'settings.php';
    class Rest {
        protected $request;
        protected $serviceName;
        protected $param;
        
        public function __construct() {  
            if ( $_SERVER['REQUEST_METHOD'] != 'POST' ) {
                //echo "Method is not post";
                $this->throwException(REQUEST_METHOD_NOT_VALID, 'Request Method is not valid');
                
            }
            $input_stream = fopen('php://input', 'r');
            $this->request = stream_get_contents($input_stream);
            $this->validateRequest($this->request);
               
        }
        
        public function validateRequest($request) {
            if ($_SERVER['CONTENT_TYPE'] != 'application/json') {
                $this->throwException(REQUEST_CONTENTTYPE_NOT_VALID, 'Requested contenty-type is not valid');      
            }
            
            $data = json_decode($this->request, true);
            
            if(!isset($data['name']) or $data['name'] == "") {
                $this->throwException(API_NAME_REQUIRED, "API name required.");
            }
            $this->serviceName = $data['name'];
            
            if(!is_array($data['param']) ) {
                $this->throwException(API_PARAM_REQUIRED, "API PARAM required.");
            }
            $this->param = $data['param'];
            //print_r($data);
            
        }
        
        public function processApi() {
            $api = new API;
            $rMethod = new reflectionMethod('API', $this->serviceName);
            if(!method_exists($api, $this->serviceName)) {
                $this->throwException(API_DOES_NOT_EXIST, "API does not exist.");
            }
            $rMethod->invoke($api);
            
        }
        
        public function validateParameter($fieldName, $value, $dataType, $required = true) {
            if ($required == true and empty($value) == true ) {
                $this->throwException(VALIDATE_PARAMETER_REQUIRED, $fieldName . " parameter is required");
            }
            
            switch ($dataType) {
                case BOOLEAN:
                    if(is_bool($value)) {
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
        
        public function throwException($code, $message) {
            header("content-type: application/json");
            $error_message = json_encode(['error' => [
                'status'    =>  $code, 
                'message'   =>  $message                
            ]]);
            echo $error_message;
            exit;
        }
        
        public function response($code, $data) {
            header ("content-type: application/json");
            $response = json_encode(['response' => [
                'status'    =>  $code, 
                'message'   =>  $data
            ]]);
            echo $response; 
            exit;
            
        }
        
        
    }
    


?>