<?php

/*
 * Security
 *
 * Here comes all security
 */
define('API_ACCESS_TOKEN_KEY', 'draYuTg32FFd8TFkdEMfVFHVtL9yhmVD4XJnSkFUkAH6VrMedmM2UND9jLkZk8NDUsZPauQFAUj8aKayGR2ZVEnQswW4tGJxLbKCsLR39ZPhKSNwjdy2R95DNsK5EEJqVM2TsPvBcXue8Jw6yGtnWCC43A6CpwaaPVd6Chs6wvy2H6vxGj3UtMRCKEsEk3CEEVhzVerdJreY9V6bdhQtg2dFksbqnSvhT64VKxYVWXJgvh6yruFjm5DVJUELstE7');
define('API_REFRESH_TOKEN_KEY', 'xCzkcXKmH7nhCqgzLcP9WbJtBWgaw3MuJmVkBJV4zn78x2JVhZ8GVMNLngfLF8pZCNSym4YvFm2PyAzLy58hjQ3aVefYTHXYH8wzp3RukbM3FwHs4kTkCUv7BGU4cNtrxWTbWeTwmkDR7nxWKJMMHrmh5qvcvQubVKwc4sE8ZyzkBDLp9g5JWCsEXdWXS2k3wtKhAtSKwHTchvnAGgtvkZTLMZ42Gfqd5eketReZRDRbqTxdsWC8zeX6feB8JurG');
define('AGE_OF_ACCESS_TOKEN', 3600);//3600);//60);          // default 60 seconds or one minute (for now)
define('AGE_OF_REFRESH_TOKEN', 24*60*60);   // default 86 400 seconds or 1 day
define('API_ALGORITHM', 'HS256');           // default HS256
define('TOKEN_TYPE_ACCESS', 1);             // Token type 1 = access and 2 = refresh. This is used in database to keep track on issued tokens
define('TOKEN_TYPE_REFRESH', 2);            // in early versions we only track access tokens
define('PASSWORD_MIN_LENGTH', 10);          // User password min lenght. Password has to contain At least a small letter, a capital letter, a number and Special character
define('USER_PASSWORD_ALGORITYM', PASSWORD_ARGON2ID); // defautl PASSWORD_ARGON2ID, other possibilities are PASSWORD_DEFAULT, PASSWORD_BCRYPT and PASSWORD_ARGON2I 
define('PASSWORD_COST', 10);                // Defines complexity of password calculation. Aim to 50 ms response from generating hash.


/*
 * Other settings
 */
define('PROCESS_TIME_OF_ONE_IMAGE',             5); // Processing time of one image. From original to full-size, medium and thumbnail. Default 5
                                                    // You should run run performanceTest() in qr.php to see how long time is needed to resize and read codes
define('LENGHT_OF_QR_CODE_HASH',                8); // Defines how many character is used in qr code hash. Default 8. Max 50. HASH is generated in bytes so length is estimated
define('URL_PATH', 'http://192.168.1.4/PhotoShare/');
define('PHOTOSHARE_PATH', '/var/www/html/PhotoShare/');
define('DATA_PATH_REAL',                  'data/');
define('IMAGE_STORAGE_PATH',    '../data/images/'); // main image path
define('IMG_SUBPATH',                   'images/'); 
define('IMG_SUBPATH_ORIGINAL',        'original/'); // sub folder to store original photos. Only needed if KEEP_ORIGINAL_PHOTO is true
define('IMG_SUBPATH_FULL_SIZE',      'full-size/'); // sub folder to store full-size photos.
define('IMG_SUBPATH_MEDIUM',            'medium/'); // sub folder to store medium photos.
define('IMG_SUBPATH_THUMBNAIL',      'thumbnail/'); // sub folder to store thumbnail photos.
define('IMG_PATH', 'http://192.168.1.4/PhotoShare/'.DATA_PATH_REAL.IMG_SUBPATH);
define('QR_CODE_IMAGE_PATH',                'qr/');

define('DATA_PATH',                    '../data/');
define('QR_PDF_PATH',                      'pdf/');
define('QR_CODE_URL_PREFIX', 'https://<your server here>/PhotoShare/album/'); // path must include /album/
define('QR_CODE_PDF_URL_PREFIX', URL_PATH.DATA_PATH_REAL.QR_PDF_PATH);
define('QR_CODE_DEFAULT_SIZE',                150); 
define('QR_CODE_GENERATE_WITHOUT_PATH',      true); //default false




/*
 * QR / EVENT 
 */
define('QR_CODE_TYPE_GALLERY',                  0); // Reqular gallery id, default 0
define('QR_CODE_TYPE_EVENT',                    1); // Event id, default 1


// Images are atomatically resized, here settings which defines maximun width/height, etc...
define('THUMBNAIL_MAX_WIDTH',           200); // thumbnail max width
define('THUMBNAIL_MAX_HEIGHT',          200); // thumbnail max height
define('MEDIUM_IMAGE_MAX_WIDTH',        800); // medium image max width. This is size where QR-codes are tried to read
define('MEDIUM_IMAGE_MAX_HEIGHT',       800); // medium image max height. This is size where QR-codes are tried to read
define('FULL_SIZE_IMAGE_MAX_WIDTH',    1920); // full-size image max width
define('FULL_SIZE_IMAGE_MAX_HEIGHT',   1920); // full-size image max height
define('JPEG_QUALITY',                   75); // JPEG compression 1 - 100 
define('KEEP_ORIGINAL_PHOTO',          true); // if true keeps orginal photo else deletes it
define('ADD_WATERMARK_TO_PHOTO',      false); // not implemented yet

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
define('EMAIL_DB',  '6');

/*
 * USERS
 * PermType and Description
 */
define('PERM_CODE_USER',                       3);
define('PERM_CODE_CODE',                       4);
define('PERM_CODE_EVENT',                      5);

define('PERM_USER',                       'user');
define('PERM_CODE',                       'code');
define('PERM_EVENT',                     'event');

define('PERM_DESCR_ADD_USER',              'add');
define('PERM_DESCR_EDIT_USER',            'edit');
define('PERM_DESCR_DELETE_USER',        'delete');
define('PERM_DESCR_LABEL_USER',          'label');

define('PERM_DESCR_GENERATE_CODE',    'generate');
define('PERM_DESCR_CLEAR_CODE',          'clear');
define('PERM_DESCR_PROCESS_CODE',      'process');
define('PERM_DESCR_LABEL_CODE',          'label');

define('PERM_DESCR_CREATE_EVENT',       'create');
define('PERM_DESCR_EDIT_EVENT',           'edit');
define('PERM_DESCR_DELETE_EVENT',       'delete');
define('PERM_DESCR_LABEL_EVENT',         'label');

/*
 * Error codes for users
 */
define('USER_HAS_NO_RIGHT',              150);
define('USER_PASSWORD',                  151);    


/**
 * General
 */

//$api_response_lang = eng; // fin / eng 


/*
 * Error Codes for rest API
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


define('QR_FAILED_GENERATE_CODES',      120);
define('QR_FAILED_CLEAR_UNUSED',        121);
define('QR_FAILED_TO_MODIFY_TYPE',      122);

define('EVENT_FAILED_TO_EDIT',           130);
define('EVENT_FAILED_TO_LIST_CODES',     131);

define('GALLERY_NOT_AVAILABLE',          150);


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
define('SUCCESS_RESPONSE',                          200);
define('SUCCESS_UPDATE_REFRESH_TOKEN',              201);



define('QR_SUCCESS_PROCESS_IMAGES',                 220);
define('QR_SUCCESS_GET_USED_HASHES',                221);
define('QR_SUCCESS_GET_UNUSED_HASHES',              222);
define('QR_SUCCESS_CODE_GENERATED',                 223);
define('QR_SUCCESS_CLEAR_UNUSED',                   224);
define('QR_PDF_GENERATED',                          225);

define('EVENT_SUCCESS_CREATED',                     240);
define('EVENT_SUCCESS_GET_LIST',                    241);
define('EVENT_SUCCESS_EDIT',                        242);
define('EVENT_SUCCESS_LIST_CODES',                  243);

define('GALLERY_SUCCESS_OBTAINED',                  250);
define('GALLERY_AVAILABLE',                         251);

/* Server Errors */

define('JWT_PROCESSING_ERROR',                      300);
define('COULD_NOT_GET_AUTHORIZATION_FROM_HEADER',   301);
define('ACCESS_TOKEN_ERROR',                        302);
define('REFRESH_TOKEN_ERROR',                       303);
define('CLASS_NOT_FOUND',                           304);
define('DATABASE_ERROR',                            305);               

?>
