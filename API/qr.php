<?php
/**
 * @version 0.0.1
 * @author Ville Kouhia
 * 
 * Changelog
 * + 25.02.2020 First appear, includes just few tests (performance) and image resizing
 * + 27.02.2020 Performance test, and generate hash (all the times are clocked on extremely slow hardware, less than 1000p in passmark)
 * + 28.02.2020 Introduction of processImages. 
 * 
 * 
 */
require "../vendor/autoload.php";
require('settings.php');

use Zxing\QrReader;

//TODO: extends api

class qr {
    
    public $database;
    protected array $qrHash;
    public array $qrCodes;
    public $qrText;
    
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
        global $qrHash;
        if (is_numeric($numberofhash) ) {
            for ( $i = 1; $i <= $numberofhash; $i++ ) {
                $this->$qrHash[$i] = bin2hex(random_bytes(LENGHT_OF_QR_CODE_HASH));
            }
        } else { return false; }
        //print_r($qrHash);       
    }
    /**
     * 
     * @param unknown $image
     * @return boolean
     * 
     * execution time aroun 0.02 seconds
     */
    public function isFileImage ($image) 
    {
        if (!in_array(mime_content_type($image), ALLOWED_IMAGE_TYPES) ) {
            return false;
        }
        return true;
    }
    
    /**
     * readQrCodeFromImage
     * 
     * @param file $image
     * @return string $text
     * 
     * Execution time with 800x800 image is around 1.35 seconds
     */
    public function readQrCodeFromImage($image) {
        if ($this->isFileImage($image)) {
            try {
                //
                $qrcode = new QrReader($image);
                $text = $qrcode->text(); //return decoded text from QR Code

            } catch (Exception $e) {
                //TODO: this->throwException();
                die('Cannot read code.');
            }
        } else {
            //TODO: Not image throw exception 
        }
        return $text;
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
     * 
     * With image size 9795KB and 4160 x 3120 pixels resizing takes around 1,67 seconds.
     * 
     * TODO:Cache; if it is already made
     */
    public function resizeImage($image, $width = MEDIUM_IMAGE_MAX_WIDTH, $height = MEDIUM_IMAGE_MAX_HEIGHT, $subpath = IMG_SUBPATH_MEDIUM, $quality = JPEG_QUALITY) 
    {
        // check if file is image
        if (!$this->isFileImage($image) ) {
            //TODO: Throw Exception
            die ("kuva ei oikeanlainen");
        }
        list($imgWidth, $imgHeight) = getimagesize($image);
        
        if ($imgWidth > $imgHeight) {
            $newWidth =  $imgWidth / ($imgWidth / $width);
            $newHeight = $imgHeight / ($imgWidth / $width);
        } else {
            $newWidth =  $imgWidth / ($imgHeight / $height);
            $newHeight = $imgHeight / ($imgHeight / $height);
        }

        $path = pathinfo($image);
        //print_r($path); // debug
        $src_img = imagecreatefromjpeg($image);
        $dest_img = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($dest_img, $src_img, 0, 0, 0, 0, $newWidth, $newHeight, $imgWidth, $imgHeight);
        
        imagejpeg($dest_img, IMAGE_STORAGE_PATH . $subpath . $path['filename'] . "." . $path['extension'], $quality);

        
        return IMAGE_STORAGE_PATH . $subpath . $path['filename'] . "." . $path['extension'];
    }
    
    /**
     * 
     */
    public function processImages()
    {
        //get array of images
        $imgs = glob(IMAGE_STORAGE_PATH . "*.{Jpg,jpg,JPG,png,PNG}", GLOB_BRACE);
        //print_r($imgs);
        
        $numberOfImages = count($imgs);
        
        //lets add file modify time to array
        $temp = array();
        for ($i = 0; $i < count($imgs); $i++ ) {
            $name = $imgs[$i];
            $mtime = filemtime($imgs[$i]);
            array_push($temp,
                array (
                'name'  => $name,
                'mtime' => $mtime)
                );
        } 
        //sort using cmpImgTime which compares unix timestamps
        usort($temp, array("qr", "cmpImgTime"));
        
        //print_r($temp);
        //echo "<br>";
        $imgbelongstocode = null;
        
        //before start processing images estimating time to accomplish and +50% 
        set_time_limit( $numberOfImages * PROCESS_TIME_OF_ONE_IMAGE * 1.5 );
        echo ("Estimated time to execute is: " . ($numberOfImages * PROCESS_TIME_OF_ONE_IMAGE * 1.5) . " seconds <br>\n");
        foreach ($temp as $item) {
            $path = pathinfo($item['name']);
            print_r ($path); //debug
            //resizeImage($image, $width = MEDIUM_IMAGE_MAX_WIDTH, $height = MEDIUM_IMAGE_MAX_HEIGHT, $subpath = IMG_SUBPATH_MEDIUM, $quality = JPEG_QUALITY) 
            $fullSize = $this->resizeImage($path['dirname'] ."/". $path['basename'], FULL_SIZE_IMAGE_MAX_WIDTH, FULL_SIZE_IMAGE_MAX_HEIGHT, IMG_SUBPATH_FULL_SIZE);
            $mediumSize = $this->resizeImage($fullSize);
            $thumbnail = $this->resizeImage($mediumSize, THUMBNAIL_MAX_WIDTH, THUMBNAIL_MAX_HEIGHT, IMG_SUBPATH_THUMBNAIL);
            
            $qrText = $this->readQrCodeFromImage($mediumSize);
            if ( $qrText ) {
                $imgbelongstocode = $qrText;
            }
            echo ("Image belongs to qr-code : " . $imgbelongstocode . "<br> \n"); //debug
            
            //TODO: add image to database and compare if it is even qr-code from this system, if not delete all sizes of image and original
            
            // if KEEP_ORIGINAL_PHOTO is true let's move it to original folder, otherwise delete it
            if ( KEEP_ORIGINAL_PHOTO ) {
                rename ($path['dirname'] ."/". $path['basename'], $path['dirname'] . "/" . IMG_SUBPATH_ORIGINAL . $path['basename']);
            } else {
                unlink ( $path['dirname'] ."/". $path['basename'] );
            }
            
            //TODO: return 201 ok so client knows to ask processing new images again
            
        }
    }
    
    function cmpImgTime($element1, $element2) 
    {
        $time1 = $element1['mtime'];
        $time2 = $element2['mtime'];
        return $time1 <=> $time2;
    }
    
    /**
     * performanceTest
     * 
     * Outputs seconds 
     * 
     * It is important to use full-size image to downscale, not original. 
     * Performance saving is from 6.7 seconds to 4.3 seconds to resize all and read qr code
     */
    public function performanceTest() 
    {
        $time_start = microtime(true);
        $resizedImage = $this->resizeImage('IMG_20200224_195430.jpg', FULL_SIZE_IMAGE_MAX_WIDTH,  FULL_SIZE_IMAGE_MAX_HEIGHT);

        $lapTime = microtime(true);
        $rstime = ($lapTime - $time_start);
        echo 'Image to full-size resize time: '.$rstime.' seconds<br>';

        $time_start2 = microtime(true);
        $resizedImageQr = $this->resizeImage($resizedImage);
        $lapTime = microtime(true);
        $rstime = ($lapTime - $time_start2);
        echo 'Image to medium resize time: '.$rstime.' seconds<br>';
        
        $time_start2 = microtime(true);
        $tnImage = $this->resizeImage($resizedImageQr, THUMBNAIL_MAX_WIDTH,  THUMBNAIL_MAX_HEIGHT);
        $lapTime = microtime(true);
        $rstime = ($lapTime - $time_start2);
        echo 'Image to thumbnail resize time: '.$rstime.' seconds<br>';
        
        $readStartTime = microtime(true);
        $this->qrText = $this->readQrCodeFromImage($resizedImageQr);
        $lapTime = microtime(true);
        $rstime = ($lapTime - $readStartTime);
        echo 'QR-code read time: '.$rstime.' seconds<br>';
        
        $time_end = microtime(true);
        $execution_time = ($time_end - $time_start);
        echo 'Total Execution Time: '.$execution_time.' seconds<br>';
    }

    
}
$time_start = microtime(true);
$koodit = new qr();
//$koodit->performanceTest();
//echo ($koodit->qrText);
//$koodit->generateHash();
//print_r($koodit->$qrHash);

$koodit->processImages();
$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo 'Total Execution Time: '.$execution_time.' seconds<br>';
?>