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
define('PASSWORD_MIN_LENGTH', 10);          // User password min lenght. Password has to contain At least a small letter, a capital letter, a number and Special character
define('USER_PASSWORD_ALGORITYM', PASSWORD_ARGON2ID); // defautl PASSWORD_ARGON2ID, other possibilities are PASSWORD_DEFAULT, PASSWORD_BCRYPT and PASSWORD_ARGON2I 
define('PASSWORD_COST', 10);                // Defines complexity of password calculation. Aim to 50 ms response from generating hash.


/*
 * Other settings
 */
define('PROCESS_TIME_OF_ONE_IMAGE',             5); // Processing time of one image. From original to full-size, medium and thumbnail. Default 5
                                                    // You should run run performanceTest() in qr.php to see how long time is needed to resize and read codes
define('LENGHT_OF_QR_CODE_HASH',               15); // Defines how many character is used in qr code hash. Default 15. Max 50.
define('IMAGE_STORAGE_PATH',    '../data/images/'); // main image path
define('IMG_SUBPATH_ORIGINAL',        'original/'); // sub folder to store original photos. Only needed if KEEP_ORIGINAL_PHOTO is true
define('IMG_SUBPATH_FULL_SIZE',      'full-size/'); // sub folder to store full-size photos.
define('IMG_SUBPATH_MEDIUM',            'medium/'); // sub folder to store medium photos.
define('IMG_SUBPATH_THUMBNAIL',      'thumbnail/'); // sub folder to store thumbnail photos.
define('QR_CODE_URL_PREFIX', 'https://<your server here>/PhotoShare/album/'); // 


// Images are atomatically resized, here settings which defines maximun width/height, etc...
define('THUMBNAIL_MAX_WIDTH',           200); // thumbnail max width
define('THUMBNAIL_MAX_HEIGHT',          200); // thumbnail max height
define('MEDIUM_IMAGE_MAX_WIDTH',        800); // medium image max width. This is size where QR-codes are tried to read
define('MEDIUM_IMAGE_MAX_HEIGHT',       800); // medium image max height. This is size where QR-codes are tried to read
define('FULL_SIZE_IMAGE_MAX_WIDTH',    1920); // full-size image max width
define('FULL_SIZE_IMAGE_MAX_HEIGHT',   1920); // full-size image max height
define('JPEG_QUALITY',                   75); // JPEG compression 1 - 100 
define('KEEP_ORIGINAL_PHOTO',          true); // if true keeps orginal photo else deletes it 

const ALLOWED_IMAGE_TYPES = array ('image/jpeg',
                                   'image/png'
                                  );


/*
 * Data Type
 *
 */
define('BOOLEAN',   '1');
define('INTEGER',   '2');
define('STRING',    '3');
define('EMAIL',     '4');
define('PASSWORD',  '5');

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
define('NO_MATCHING_REFRESH_TOKEN',     110);
define('TOO_MANY_TOKENS',               111);
define('VALIDATE_PARAMETER_EMAIL',      112);
define('PASSWORD_NOT_COMPLEX_ENOUGH',   113);
define('QR_CODE_DO_NOT_EXIST',          114);


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
define('REFRESH_TOKEN_ERROR',                       303);
define('CLASS_NOT_FOUND',                           304);
define('DATABASE_ERROR',                            305);               

?>
