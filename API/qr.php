<?php
/**
 * @version 0.0.1
 * @author Ville Kouhia
 * 
 * Changelog
 * + 25.02.2020 First appear, includes just few tests (performance) and image resizing
 * + 27.02.2020 Performance test, and generate hash (all the times are clocked on extremely slow hardware, less than 1000p in passmark)
 * + 28.02.2020 Introduction of processImages. 
 * + 05.03.2020 Generating qr-code images png
 * + 16.04.2020 Integration with api started
 * 
 */

// These three require are needed only when standalon
//require "../vendor/autoload.php"; 
//require('settings.php');
//require('database.php');

use Zxing\QrReader;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\ErrorCorrectionLevel;


class qr extends Api {
    
    public $database;
    protected array $qrHash; // not needed if used db queries instead
    public array $qrCodes;
    public $qrText;
    
    protected $usedHashes;
    
    public function __construct() 
    {

    }
    
    /**
     * generateQrCodes
     * 
     * @param number $numberofhash
     * @param number $fatherId
     * @return boolean
     */
    public function generateQrCodes($numberofhash = 10, $fatherId = 0, $qrCodeSize = QR_CODE_DEFAULT_SIZE) 
    {
        global $qrHash;
        // let's generate hash
        if (is_numeric($numberofhash) ) {
            for ( $i = 1; $i <= $numberofhash; $i++ ) {
                $this->$qrHash[$i] = strtoupper(bin2hex(random_bytes(LENGHT_OF_QR_CODE_HASH/2)));
            }
        } else { 
            return false; 
        }
        //print_r($qrHash);       
        
        // insert into db
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            $sql = ("INSERT INTO hash(FatherId,
                                        Hash,
                                        Type,
                                        Disabled,
                                        OwnerId,
                                        CreatedOn,
                                        ModDate) 
                                VALUES (:FatherId,
                                        :Hash,
                                        :Type,
                                        :Disabled,
                                        :OwnerId,
                                        :CreatedOn, 
                                        :ModDate)");
            $stmt = $this->database->prepare($sql);
            
            $date = new DateTime();
            $createdOn = $date->format('Y-m-d H:i:s');
            $modDate = $createdOn;
            $hashType = 0;
            $disabled = 0;
            //TODO: When integrating with api getUserid from db
            $ownerId = 2;
            
            //add each hash to db
            for ( $i = 1; $i <= count($this->$qrHash); $i++ ) {
    
                $stmt->bindParam(':FatherId',   $fatherId,          PDO::PARAM_STR);
                $stmt->bindParam(':Hash',       $this->$qrHash[$i], PDO::PARAM_STR);
                $stmt->bindParam(':Type',       $hashType,          PDO::PARAM_STR);
                $stmt->bindParam(':Disabled',   $disabled,          PDO::PARAM_STR);
                $stmt->bindParam(':OwnerId',    $ownerId,           PDO::PARAM_STR);
                $stmt->bindParam(':CreatedOn',  $createdOn,         PDO::PARAM_STR);
                $stmt->bindParam(':ModDate',    $modDate,           PDO::PARAM_STR);
                
                $stmt->execute();
            }
            
        } catch (Exception $e) {
            //TODO: uncomment when integratin with API
            //$this->throwException(DATABASE_ERROR, "Database insert error.");
        }
        
        //generate qr-images. This is pretty fast, less than 1.2 sec for 20 qr-images
        for ( $i = 1; $i <= count($this->$qrHash); $i++ ) {
            //generate QR code with prefix URL
            $qrCode = new QrCode(QR_CODE_URL_PREFIX . $this->$qrHash[$i]);
            $qrCode->setSize($qrCodeSize);
            $qrCode->setWriterByName('png');
            $qrCode->setMargin('10');
            $qrCode->setEncoding('UTF-8');
            $qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
            $qrCode->writeFile(IMAGE_STORAGE_PATH . QR_CODE_IMAGE_PATH . $this->$qrHash[$i].'_url.png');
            
            // generates code just with hash if it is also required 
            if (QR_CODE_GENERATE_WITHOUT_PATH) {
                $qrCode->setText($this->$qrHash[$i]);
                $qrCode->writeFile(IMAGE_STORAGE_PATH . QR_CODE_IMAGE_PATH . $this->$qrHash[$i].'.png');
            }
            
        }
        
        return true;
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
    public function readQrCodeFromImage($image) 
    {
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
        
        if (ADD_WATERMARK_TO_PHOTO) {
            //TODO: Add optional watermark to photo
        }
        
        imagejpeg($dest_img, IMAGE_STORAGE_PATH . $subpath . $path['filename'] . "." . $path['extension'], $quality);
        
        
        return IMAGE_STORAGE_PATH . $subpath . $path['filename'] . "." . $path['extension'];
    }
    
    public function insertImageToDb() 
    {
        // this is now in processImages
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
        
        //Stores last QRCode
        $imgbelongstocode = null;
        $imghashid = null;
        
        //before start processing images estimating time to accomplish and +50% 
        set_time_limit( $numberOfImages * PROCESS_TIME_OF_ONE_IMAGE * 1.5 );
        // echo ("Estimated time to execute is: " . ($numberOfImages * PROCESS_TIME_OF_ONE_IMAGE * 1.5) . " seconds <br>\n"); //debug
        $db = new Database();
        $this->database = $db->connect();
        
        // Start processing
        $i = 0;
        foreach ($temp as $item) {
            $path = pathinfo($item['name']);
            // print_r ($path); //debug
            //resizeImage($image, $width = MEDIUM_IMAGE_MAX_WIDTH, $height = MEDIUM_IMAGE_MAX_HEIGHT, $subpath = IMG_SUBPATH_MEDIUM, $quality = JPEG_QUALITY) 
            $fullSize = $this->resizeImage($path['dirname'] ."/". $path['basename'], FULL_SIZE_IMAGE_MAX_WIDTH, FULL_SIZE_IMAGE_MAX_HEIGHT, IMG_SUBPATH_FULL_SIZE);
            $mediumSize = $this->resizeImage($fullSize);
            $thumbnail = $this->resizeImage($mediumSize, THUMBNAIL_MAX_WIDTH, THUMBNAIL_MAX_HEIGHT, IMG_SUBPATH_THUMBNAIL);
            
            $qrText = $this->readQrCodeFromImage($mediumSize);
            
            // collect hash from captured URL
            //echo ("QRTEXT: " . $qrText. "\n");
            preg_match('/(?<=\/album\/)[a-zA-Z0-9]{1,50}/', $qrText, $matches);
            //print_r($matches);
                
                //if problems with regular expression use this simple explode
                //$parsedHash = explode('/album/', $qrText);
                //$matches = $parsedHash[1];

            // if qr code was able to read from image
            if ( $qrText ) {
                //query db to see if hash matches
                try {
                    $sql = ("SELECT Id, FatherId, Hash FROM hash
                                                   WHERE Hash = :Hash");
                    
                    $stmt = $this->database->prepare($sql);
                    $stmt->bindParam(':Hash',   $matches[0],          PDO::PARAM_STR);
                    
                    $stmt->execute();
                    $dbHash = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (! is_array($dbHash)) {
                        // TODO: uncomment when integrating with API throwException or warning
                        die("This QR-code doesn't exists our database.");
                        //$this->throwException(QR_CODE_DO_NOT_EXIST, "This QR-code doesn't exists our database.");
                    }
                    
                    //store last qr code
                    $imgbelongstocode = $dbHash['Hash'];
                    $imghashid = $dbHash['Id'];
                    //echo ("Image belongs to qr-code : " . $imgbelongstocode . "<br> \n"); //debug
                } catch (Exception $e) {
                    die("DB ERROR: " . $e->getMessage());
                    //TODO: uncomment when integrating with API
                    //$this->throwException(DATABASE_ERROR, "Database select error.");
                }   
            }
            //we have to rename image, include hash in image name
            rename($fullSize,       IMAGE_STORAGE_PATH .    IMG_SUBPATH_FULL_SIZE . $imgbelongstocode . "_" . $path['basename']);
            rename($mediumSize,     IMAGE_STORAGE_PATH .    IMG_SUBPATH_MEDIUM .    $imgbelongstocode . "_" . $path['basename']);
            rename($thumbnail,      IMAGE_STORAGE_PATH .    IMG_SUBPATH_THUMBNAIL . $imgbelongstocode . "_" . $path['basename']);
            
            //TODO: add image to database
            try {
                $sql = ("INSERT INTO images(HashId,
                                        NameOnDisk,
                                        Deleted,
                                        CreatedOn)
                                VALUES (:HashId,
                                        :NameOnDisk,
                                        :Deleted,
                                        :CreatedOn)");
                $stmt = $this->database->prepare($sql);
                
                $date = new DateTime();
                $createdOn = $date->format('Y-m-d H:i:s');;
                $deleted = 0;
                $nameondisk = $imgbelongstocode . "_" . $path['basename'];
                    
                $stmt->bindParam(':HashId',     $imghashid,         PDO::PARAM_STR);
                $stmt->bindParam(':NameOnDisk', $nameondisk,        PDO::PARAM_STR);
                $stmt->bindParam(':Deleted',    $deleted,           PDO::PARAM_STR);
                $stmt->bindParam(':CreatedOn',  $createdOn,         PDO::PARAM_STR);
                
                $stmt->execute();
                
                
            } catch (Exception $e) {
                //TODO: uncomment when integratin with API
                $this->throwException(DATABASE_ERROR, "Database insert error.");
            }
            
            // if KEEP_ORIGINAL_PHOTO is true let's move it to IMG_SUBPATH_ORIGINAL folder, otherwise delete it
            if ( KEEP_ORIGINAL_PHOTO ) {
                rename ($path['dirname'] ."/". $path['basename'], $path['dirname'] . "/" . IMG_SUBPATH_ORIGINAL . $path['basename']);
                //TODO: comment above and uncomment below when in production to enable orginal photo. Easier way to test when using above.
                //rename ($path['dirname'] ."/". $path['basename'], $path['dirname'] . "/" . IMG_SUBPATH_ORIGINAL . $imgbelongstocode . "_" . $path['basename']);
            } else {
                unlink ( $path['dirname'] ."/". $path['basename'] );
            }
            
            $i++;
        } // END processing / end foreach
        
        // returns 220 so client knows to ask more
        $this->response(QR_SUCCESS_PROCESS_IMAGES, $i . " image(s) were processed");
        
        $db->disconnect();
        //TODO: Save to db information from last used HasId and/or hash 
        
    }
    
    /**
     * getUsedHashes
     * 
     * @param number $limit_start
     * @param number $limit_end
     */
    public function getUsedHashes($limit_start = 0, $limit_end = 100) 
    {
        $db = new Database();
        $this->database = $db->connect();
        
        try {
            $sql = ("SELECT DISTINCT h.Hash, h.CreatedOn FROM hash h, images i WHERE i.HashId = h.Id ORDER BY ModDate desc LIMIT :start, :end");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":stat", $limit_start);
            $stmt->bindParam(":end", $limit_end);
            
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->usedHashes['hash'][$row['Hash']]['hash'] = $row['Hash'];
                $this->usedHashes['hash'][$row['Hash']]['createdon'] = $row['CreatedOn'];
            }
            
            $this->response(QR_SUCCESS_GET_USED_HASHES, $this->usedHashes);
        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
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
$koodit->generateQrCodes();
print_r($koodit->$qrHash);

/*
$koodit->processImages();
*/

$time_end = microtime(true);
$execution_time = ($time_end - $time_start);
echo 'Total Execution Time: '.$execution_time.' seconds<br>';



?>