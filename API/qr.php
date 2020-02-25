<?php
/**
 * @version 0.0.1
 * @author Ville Kouhia
 * 
 * Changelog
 * + First appear, includes just few tests (performance) and image resizing
 *
 */
require "../vendor/autoload.php";
require('settings.php');

use Zxing\QrReader;

class qr {
    
    public $database;
    public array $qrCodes;
    
    public function __construct() 
    {
        
        
    }
    
    public function generateQrCodes() 
    {
        
    }
    /**
     * 
     * @param number $numberofhash
     */
    public function generateHash($numberofhash = 10) 
    {
//        ini_set('memory_limit', '4096M');
 //       ini_set('max_execution_time', 300);
  //      set_time_limit(300);
       /* if (is_numeric($numberofhash) )
            for ( $i = 0; $i <= $numberofhash; $i++ ) {
                $bytes[$i] = bin2hex(random_bytes(LENGHT_OF_QR_CODE_HASH));
            }*/
        //print_r($bytes);
        
       $qrcode = new QrReader('/mnt/stuff/projects/eclipse/PhotoShare/API/no-qr.jpg');
       $text = $qrcode->text(); //return decoded text from QR Code
       if($text) {
           die ("löytyy koodi");
       }
       die ("ei löydy");
       echo $text;

       
    }
    
    public function isFileImage ($image) 
    {
        if (!in_array(mime_content_type($image), ALLOWED_IMAGE_TYPES) ) {
            return false;
        }
        return true;
    }
    
    /**
     * 
     * resizeImage 
     * 
     * Keeps always aspectratio and do not cut images
     * 
     * @param string $image     // image filename without path
     * @param string $width     // maximum allowed width
     * @param string $height    // maximum allowed height
     * @param string $quality   // jpeg quality default 75
     */
    public function resizeImage($image, $width = MEDIUM_IMAGE_MAX_WIDTH, $height = MEDIUM_IMAGE_MAX_HEIGHT, $quality = JPEG_QUALITY) 
    {
        if (!$this->isFileImage(IMAGE_STORAGE_PATH . $image) ) {
            //TODO: Throw Exception
            die ("kuva ei oikeanlainen");
        }
        list($imgWidth, $imgHeight) = getimagesize(IMAGE_STORAGE_PATH . $image);
        
        if ($imgWidth > $imgHeight) {
            $newWidth =  $imgWidth / ($imgWidth / $width);
            $newHeight = $imgHeight / ($imgWidth / $width);
        } else {
            $newWidth =  $imgWidth / ($imgHeight / $height);
            $newHeight = $imgHeight / ($imgHeight / $height);
        }
        
        $path = pathinfo($image);
        //print_r($path); // debug
        $src_img = imagecreatefromjpeg(IMAGE_STORAGE_PATH . $image);
        $dest_img = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $newWidth, $newHeight, $imgWidth, $imgHeight);
        
        imagejpeg($dest_img, IMAGE_STORAGE_PATH . $path['filename'] . "_" . $newWidth . "x" . $newHeight . ".jpg", $quality);
        
    }
    
    
    public function readCodeFromImage($image) 
    {
        
        
    }
    
}

$koodit = new qr();
//$koodit->generateHash();

$koodit->resizeImage('IMG_20200224_195430.jpg', 1280, 780);



?>