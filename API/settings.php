<?php

/*
 * Security
 *
 * Here comes all security
 * TODO: watch
 */
define('API_ACCESS_TOKEN_KEY', 'draYuTg32FFd8TFkdEMfVFHVtL9yhmVD4XJnSkFUkAH6VrMedmM2UND9jLkZk8NDUsZPauQFAUj8aKayGR2ZVEnQswW4tGJxLbKCsLR39ZPhKSNwjdy2R95DNsK5EEJqVM2TsPvBcXue8Jw6yGtnWCC43A6CpwaaPVd6Chs6wvy2H6vxGj3UtMRCKEsEk3CEEVhzVerdJreY9V6bdhQtg2dFksbqnSvhT64VKxYVWXJgvh6yruFjm5DVJUELstE7');
define('API_REFRESH_TOKEN_KEY', 'xCzkcXKmH7nhCqgzLcP9WbJtBWgaw3MuJmVkBJV4zn78x2JVhZ8GVMNLngfLF8pZCNSym4YvFm2PyAzLy58hjQ3aVefYTHXYH8wzp3RukbM3FwHs4kTkCUv7BGU4cNtrxWTbWeTwmkDR7nxWKJMMHrmh5qvcvQubVKwc4sE8ZyzkBDLp9g5JWCsEXdWXS2k3wtKhAtSKwHTchvnAGgtvkZTLMZ42Gfqd5eketReZRDRbqTxdsWC8zeX6feB8JurG');
define('AGE_OF_ACCESS_TOKEN', 60);          // default 60 seconds or one minute (for now)
define('AGE_OF_REFRESH_TOKEN', 24*60*60);   // default 86 400 seconds or 1 day
define('API_ALGORITHM', 'HS256');           // default HS256
define('TOKEN_TYPE_ACCESS', 1);             // Token type 1 = access and 2 = refresh. This is used in database to keep track on issued tokens
define('TOKEN_TYPE_REFRESH', 2);            // in early versions we only track refresh tokens


/* database password examplePass */

/*
 * Data Type
 *
 * TODO: add email, phonenumber, ect...
 */
define('BOOLEAN',   '1');
define('INTEGER',   '2');
define('STRING',    '3');

/**
 * General
 */

//$api_response_lang = eng; // fin / eng 


/*
 * Error Codes for rest API
 *
 *
 */

define('REQUEST_METHOD_NOT_VALID',      100);
define('REQUEST_CONTENTTYPE_NOT_VALID', 101);
define('REQUEST_NOT_VALID',             102);
define('VALIDATE_PARAMETER_REQUIRED',   103);
define('VALIDATE_PARAMETER_DATATYPE',   104);
define('API_NAME_REQUIRED',             105);
define('API_PARAM_REQUIRED',            106);
define('API_DOES_NOT_EXIST',            107);
define('INVALID_USER_PASS',             108);
define('USER_IS_DISABLED',              109);

/**
 * TODO: Do we need general place for error messages?
 * @var array $error_messages
 */
$error_messages = array (
    "REQUEST_METHOD_NOT_VALID"      => array   ("code" => 100,
                                                "fin" => "Pyyntö ei ole kelvollinen",
                                                "eng" => "Request Method is not valid."
                                                ),
    "REQUEST_CONTENTTYPE_NOT_VALID" => array   ("code" => 101,
                                                "fin" => "Pyydetty sisältötyyppi ei ole kelvollinen",
                                                "eng" => "Requested content-type is not valid"
                                                ),
    "REQUEST_NOT_VALID"             => array   ("code" => 102,
                                                "fin" => "",
                                                "eng" => "h"
                                                ),
    "VALIDATE_PARAMETER_REQUIRED"   => array   ("code" => 103,
                                                "fin" => "",
                                                "eng" => "h"
                                                ),
    "VALIDATE_PARAMETER_DATATYPE"   => array   ("code" => 101,
                                                "fin" => "",
                                                "eng" => "h"
                                                ),
    "API_NAME_REQUIRED"             => array   ("code" => 101,
                                                "fin" => "",
                                                "eng" => "h"
                                                ),
    "REQUEST_CONTENTTYPE_NOT_VALID" => array   ("code" => 101,
                                                "fin" => "",
                                                "eng" => "h"
                                                )
    
    );
//print_r($error_messages);


/**
 * Success responses
 * 
 */
define('SUCCESS_RESPONSE',      200);



/* Server Errors */

define('JWT_PROCESSING_ERROR',                      300);
define('COULD_NOT_GET_AUTHORIZATION_FROM_HEADER',   301);
define('ACCESS_TOKEN_ERROR',                        302);
define('REFRESH_TOKEN_ERROR',                       302);
define('CLASS_NOT_FOUND',                           304);

?>