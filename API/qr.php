<?php
/**
 * @version 1.0
 * @author Ville Kouhia
 * 
 * Changelog
 * + 25.02.2020 First appear, includes just few tests (performance) and image resizing
 * + 27.02.2020 Performance test, and generate hash (all the times are clocked on extremely slow hardware, less than 1000p in passmark)
 * + 28.02.2020 Introduction of processImages. 
 * + 05.03.2020 Generating qr-code images png
 * + 16.04.2020 Integration with api started
 * + 24.04.2020 get/generate/clear/print codes
 * + 05.06.2020 First realease version 1.0
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
    
    public $database;           // db connection
    protected array $qrHash;    // used to generate codes
    public array $qrCodes;      // 
    public $qrText;             // text from image
    
    protected array $usedHash;  // usedHashes 
    protected array $unusedHash;// unusedHashesh
    
    protected array $images;    // images
    
    protected $event_id;        // event id
    protected $event_code;      // event code
    protected $event_name;      // event name
    protected $event_descr;     // event description
    
    
    public function __construct() 
    {
       
    }
    
    /**
     * generateQrCodes
     * 
     * @param number $userid
     * @param number $numberofhash
     * @param number $fatherId
     * @return boolean
     * 
     * generates codes and adds to database. Generates qr-images as well
     * 
     */
    public function generateQrCodes($userid, $numberofhash = 10, $fatherId = 0, $qrCodeSize = QR_CODE_DEFAULT_SIZE) 
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
            // normal hash = 0, event = 1
            $hashType = 0;
            $disabled = 0;

            $ownerId = $userid;
            
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
            
            $this->throwException(DATABASE_ERROR, "Database insert error.");
        }
        
        //generate qr-images. This is pretty fast, less than 1.2 sec for 20 qr-images
        for ( $i = 1; $i <= count($this->$qrHash); $i++ ) {
            //generate QR code with prefix URL
            $qrCode = new QrCode(QR_CODE_URL_PREFIX . $this->$qrHash[$i]);
            $qrCode->setSize($qrCodeSize);
            $qrCode->setWriterByName('png');
            $qrCode->setMargin('10');
            $qrCode->setEncoding('UTF-8');
            
            // better change to read codes with default settings
            //$qrCode->setErrorCorrectionLevel(ErrorCorrectionLevel::HIGH());
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
     * isFileImage
     * 
     * Checks if file is really image or something else
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
     * Cache not implemented yet in version 1.0
     */
    public function resizeImage($image, $width = MEDIUM_IMAGE_MAX_WIDTH, $height = MEDIUM_IMAGE_MAX_HEIGHT, $subpath = IMG_SUBPATH_MEDIUM, $quality = JPEG_QUALITY) 
    {
        // check if file is image
        if (!$this->isFileImage($image) ) {
            $this->throwException(FILE_IS_NOT_IMAGE, "File is not an image");
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
     * processImages($userid)
     * 
     * process images to user.
     * 
     * Processing is executed in order images were modified.
     * Image code must include album forexample https://doma.in/PhotoShare/client/album/12345678
     * 
     * 
     */
    public function processImages($userid)
    {
        $fatherId = 0;
        if ( ($this->getEventCode() != null) && $this->checkGalleryAvailability() ) {
            $fatherId = $this->getHashId($this->getEventCode());
        }
        
        //get array of images
        // TODO: user specific locations; not implemented in version 1.0
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
        // first get last code which were under process
        $imgbelongstocode = $this->getLastUsedHash($userid);
        $imghashid = $this->getHashId($imgbelongstocode);
        //echo ('$imgbelongstocode: '. $imgbelongstocode ."\n");
        //echo ('$imghashid: '. $imghashid ."\n");
        
        $i = 0; // count images processed
        foreach ($temp as $item) {
            $path = pathinfo($item['name']);
            // print_r ($path); //debug
            //resizeImage($image, $width = MEDIUM_IMAGE_MAX_WIDTH, $height = MEDIUM_IMAGE_MAX_HEIGHT, $subpath = IMG_SUBPATH_MEDIUM, $quality = JPEG_QUALITY) 
            $fullSize = $this->resizeImage($path['dirname'] ."/". $path['basename'], FULL_SIZE_IMAGE_MAX_WIDTH, FULL_SIZE_IMAGE_MAX_HEIGHT, IMG_SUBPATH_FULL_SIZE);
            $mediumSize = $this->resizeImage($fullSize);
            $thumbnail = $this->resizeImage($mediumSize, THUMBNAIL_MAX_WIDTH, THUMBNAIL_MAX_HEIGHT, IMG_SUBPATH_THUMBNAIL);
            
            $qrText = $this->readQrCodeFromImage($mediumSize);
            //echo("QRTEXT: " . $qrText);
            
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
                       
                        //die("This QR-code doesn't exists our database.");
                        $this->throwException(QR_CODE_DO_NOT_EXIST, "This QR-code doesn't exists our database.");
                    }

                    //store last qr code
                    $imgbelongstocode = $dbHash['Hash'];
                    $imghashid = $dbHash['Id'];
                    //echo ("Image belongs to qr-code : " . $imgbelongstocode . "<br> \n"); //debug
               
                } catch (Exception $e) {
                    //die("DB ERROR: " . $e->getMessage());
                    
                    $this->throwException(DATABASE_ERROR, "Database select error.");
                }
     
            }
            //we have to rename image, include hash in image name
            rename($fullSize,       IMAGE_STORAGE_PATH .    IMG_SUBPATH_FULL_SIZE . $imgbelongstocode . "_" . $path['basename']);
            rename($mediumSize,     IMAGE_STORAGE_PATH .    IMG_SUBPATH_MEDIUM .    $imgbelongstocode . "_" . $path['basename']);
            rename($thumbnail,      IMAGE_STORAGE_PATH .    IMG_SUBPATH_THUMBNAIL . $imgbelongstocode . "_" . $path['basename']);
            
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
                
                //echo("INFO: " . $imghashid.$nameondisk.$deleted.$createdOn);
                
                $stmt->bindParam(':HashId',     $imghashid,         PDO::PARAM_STR);
                $stmt->bindParam(':NameOnDisk', $nameondisk,        PDO::PARAM_STR);
                $stmt->bindParam(':Deleted',    $deleted,           PDO::PARAM_STR);
                $stmt->bindParam(':CreatedOn',  $createdOn,         PDO::PARAM_STR);
                
                $stmt->execute();
                //$stmt->debugDumpParams(); //debug
                // if fatherid is something else than zero. Lets combine hash to an event
                // Update hash fatherid to an event id
                if ( $fatherId != 0 ) {
                    //echo ("UPDATE FATHERID: ". $fatherId ."\n");
                    //echo ('$imghashid '. $imghashid ."\n");
                    $sql = ("UPDATE hash SET FatherId = :fatherid WHERE Id = :hashid");
                    $stmt = $this->database->prepare($sql);
                    $stmt->bindParam(':hashid',    $imghashid,         PDO::PARAM_STR);
                    $stmt->bindParam(':fatherid',  $fatherId,          PDO::PARAM_STR);
                    //$stmt->debugDumpParams(); //debug
                    $stmt->execute();
                }
  
            } catch (Exception $e) {
                $this->throwException(DATABASE_ERROR, "Database insert error..");
            }
            
            // if KEEP_ORIGINAL_PHOTO is true let's move it to IMG_SUBPATH_ORIGINAL folder, otherwise delete it
            if ( KEEP_ORIGINAL_PHOTO ) {
                // Line below: do not rename orginal to helps with debug and testing
                //rename ($path['dirname'] ."/". $path['basename'], $path['dirname'] . "/" . IMG_SUBPATH_ORIGINAL . $path['basename']);
                rename ($path['dirname'] ."/". $path['basename'], $path['dirname'] . "/" . IMG_SUBPATH_ORIGINAL . $imgbelongstocode . "_" . $path['basename']);
            } else {
                unlink ( $path['dirname'] ."/". $path['basename'] );
            }
            
            $i++;
        } // END processing / end foreach
        
        
        // Update db for last used ID
        $this->setLastUsedHash($userid, $imgbelongstocode);
        
        // return number of images were processed
        return $i . " image(s) were processed";
        
        
        $db->disconnect();

        
    }
    
    /**
     * getHashId
     * 
     * gets equalient id for a hash from db
     * 
     * @param $hash
     * @return hash[id]
     */
    public function getHashId($hash) 
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            $sql = ("SELECT Id FROM hash WHERE Hash = :hash");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':hash',   $hash,        PDO::PARAM_STR);
           
            $stmt->execute();
            
            $response = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
        }
        if (isset($response['Id'])) {
            return $response['Id'];
        }
        return false;
    }
    
    /**
     * getLastUsedHash
     * 
     * gets last used hash in processing, stored in settings table
     * 
     * @param  $userid
     * @return lastusedhash
     */
    public function getLastUsedHash($userid) 
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            // we want get setting which is named "lasthash"
            $stype = "lasthash";
            $sql = ("SELECT Value FROM Settings WHERE UserId = :userid AND SettingType = :stype");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':userid',   $userid);
            $stmt->bindParam(':stype',    $stype);
            
            $stmt->execute();
            
            $hash = $stmt->fetch(PDO::FETCH_ASSOC);
            //$stmt->debugDumpParams(); //debug
        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
        }
        
        return $hash['Value'];
    }
    
    /**
     * setLastUsedHash
     * 
     * sets last used hash to db to an user
     * 
     * @param $userid
     * @param $lasthash
     * @return boolean true or failed throwException
     */
    public function setLastUsedHash($userid, $lasthash)
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            // we want get setting which is named "lasthash"
            $stype = "lasthash";
            $sql = ("UPDATE Settings SET Value = :value WHERE UserId = :userid AND SettingType = :stype");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':userid',   $userid);
            $stmt->bindParam(':value',    $lasthash);
            $stmt->bindParam(':stype',    $stype);
            //$stmt->debugDumpParams(); //debug
            
            $stmt->execute();
        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
        }
        
        return true;
    }
    
    /**
     * insertLastUsedHash
     * 
     * inserts lastu used hash to db to an user
     * 
     * @param $userid
     * @param $lasthash
     * @return true if success else ThrowException
     */
    public function insertLastUsedHash($userid, $lasthash) 
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            // we want insert setting which is named "lasthash"
            $stype = "lasthash";
            $sql = ("INSERT INTO Settings  (UserId, Value, SettingType) 
                                    VALUES (:userid, :value, :stype)");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(':userid',   $userid);
            $stmt->bindParam(':value',    $lasthash);
            $stmt->bindParam(':stype',    $stype);
            //$stmt->debugDumpParams(); //debug
            
            $stmt->execute();
        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
        }
        
        return true;
    }
    
    /**
     * getUsedHashes
     * 
     * @param number $limit_start
     * @param number $limit_end
     */
    public function getUsedHash($userid, $disabled = 0, $limit_start = 0, $limit_end = 100) 
    {
        $db = new Database();
        $this->database = $db->connect();
        
        try {
            $sql = ("SELECT DISTINCT h.Id, h.Hash, h.CreatedOn 
                                    FROM hash h, 
                                         images i 
                                    WHERE 
                                        i.HashId = h.Id AND 
                                        h.OwnerId = :userid AND
                                        h.Disabled = :disabled
                                    ORDER BY h.Id desc 
                                    LIMIT :start, :end");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":start",      $limit_start,   PDO::PARAM_INT);
            $stmt->bindParam(":end",        $limit_end,     PDO::PARAM_INT);
            $stmt->bindParam(":userid",     $userid,        PDO::PARAM_STR);
            $stmt->bindParam(":disabled",   $disabled,      PDO::PARAM_STR);
            
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->usedHash['hash'][$row['Id']]['id'] = $row['Id'];
                $this->usedHash['hash'][$row['Id']]['hash'] = $row['Hash'];

            }
            return true;

        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
        }
         
    }
    
    /**
     * getUnusedHash
     * 
     * get unused hash list from db. On default will get codes that are tied to event and first 100
     * 
     * @param $userid
     * @param $type
     * @param $disabled
     * @param $limit_start
     * @param $limit_end
     * @return boolean
     */
    public function getUnusedHash($userid, $type = QR_CODE_TYPE_GALLERY, $disabled = 0, $limit_start = 0, $limit_end = 100)
    {
        $db = new Database();
        $this->database = $db->connect();
        
        // only unused so nothing but default. 
        // hash is used two ways; as a event or as an image(s)
        
        try {
            $sql = ("SELECT h.Id, h.Hash, h.Name, h.Descr, h.CreatedOn 
                                    FROM hash h 
                                    WHERE h.Id 
                                    NOT IN (
                                        SELECT i.HashId 
                                        FROM images i
                                    ) AND 
                                    h.OwnerId = :userid AND
                                    h.Disabled = :disabled AND
                                    h.type = :type
                                    ORDER BY h.Id desc 
                                    LIMIT :start, :end");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":start",      $limit_start,   PDO::PARAM_INT);
            $stmt->bindParam(":end",        $limit_end,     PDO::PARAM_INT);
            $stmt->bindParam(":userid",     $userid,        PDO::PARAM_STR);
            $stmt->bindParam(":type",       $type,          PDO::PARAM_STR);
            $stmt->bindParam(":disabled",   $disabled,      PDO::PARAM_STR);
            
            $stmt->execute();

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //echo ("i: " .$i. "\n");
                $this->unusedHash['hash'][$row['Id']]['id'] = $row['Id'];
                $this->unusedHash['hash'][$row['Id']]['hash'] = $row['Hash'];
                
                // if it is event add name and descr to output
                if ( $type == QR_CODE_TYPE_EVENT ) {
                    $this->unusedHash['hash'][$row['Id']]['name'] = $row['Name'];
                    $this->unusedHash['hash'][$row['Id']]['descr'] = $row['Descr'];
                }
            }
            
            return true;

        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
        }
        
        return false;
        
    }

    /**
     * unusedHash
     * 
     * @return unusedhash
     */
    public function unusedHash()
    {
        if ( !empty($this->unusedHash) ) {
            return $this->unusedHash;
        }
       
    }
    
    /**
     * usedHash
     * 
     * @return usedHash
     */
    public function usedHash()
    {
        if ( !empty($this->usedHash) ) {
            return $this->usedHash;
        }
       
    }
    
    /**
     * deleteUnusedCodes
     * 
     * deletes unused codes
     * 
     * @param  $userid
     * @param  $disabled
     * @param  $type
     * @return boolean / or catch error
     */
    public function deleteUnusedCodes($userid, $disabled = 0, $type = 0) 
    {
        $db = new Database();
        $this->database = $db->connect();
        
        try {
            $sql = ("DELETE FROM hash WHERE Id NOT IN (SELECT HashId FROM images) AND OwnerId = :userid AND Disabled = :disabled AND Type = :type ");
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":userid",     $userid,        PDO::PARAM_STR);
            $stmt->bindParam(":disabled",   $disabled,      PDO::PARAM_STR);
            $stmt->bindParam(":type",       $type,          PDO::PARAM_STR);
            
            $stmt->execute();
            
            return true;
           
        } catch (Exception $e) {
            $this->ThrowException(DATABASE_ERROR, $e);
        }
        return false;
    }
    
    /**
     * createPdfFromUnusedCodes
     * 
     * 
     * @param $userid
     * @return string
     */
    public function createPdfFromUnusedCodes($userid)
    {
        $pdf = new TCPDF('P', 'px', 'A4', true, 'UTF-8', false);
        
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('PhotoShare');
        $pdf->SetTitle('QR Codes');
        $pdf->SetSubject('QR Code');

        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        //margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        //$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        $pdf->AddPage();
        
        $pdf->setJPEGQuality(100);
        
        $this->getUnusedHash($userid);
        
        $hash = $this->unusedHash();
        
        //print_r($hash['hash']);
        
        $margin = 30;
        $default = 30;
        $x = $default;
        $y = $default;
        
//        echo ("Count: " . count($hash));
        // TODO: DO a proper layout
//        for ( $i = 0; $i < count($hash['hash']); $i++ ) {
        $i = 0; // rows
        $k = 0; // lines
        foreach ($hash['hash'] as $key => $value ) {
            if ($i%3==0) {
                $y = $y + $margin + QR_CODE_DEFAULT_SIZE;
                $x = $default;
                $i = 0;
                $k++;
            }
            // code without url
            //$pdf->Image(IMAGE_STORAGE_PATH.QR_CODE_IMAGE_PATH.$value['hash'].'.png', $x, $y, QR_CODE_DEFAULT_SIZE, QR_CODE_DEFAULT_SIZE, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
            // code with url
            $pdf->Image(IMAGE_STORAGE_PATH.QR_CODE_IMAGE_PATH.$value['hash'].'_url.png', $x, $y, QR_CODE_DEFAULT_SIZE, QR_CODE_DEFAULT_SIZE, 'PNG', '', '', false, 300, '', false, false, 0, false, false, false);
            if ($k%3==0) {
                $y = $default;
                $x = $default;
                $k = 0;
                $pdf->AddPage();
                $i--;
            }
            
            $x = $x + $margin + QR_CODE_DEFAULT_SIZE;
            $i++;
            
        }
        ob_end_clean();
        $date = new DateTime();
        $createdOn = $date->format('YmdHis');
        $pdf->Output(PHOTOSHARE_PATH.'data/'.QR_PDF_PATH.$userid.'_'.$createdOn.'.pdf', 'F');
        
        return QR_CODE_PDF_URL_PREFIX.$userid.'_'.$createdOn.'.pdf';
        //$pdf->Output('example_66.pdf', 'I');
    }
    
    /**
     * modifyHashType
     * 
     * @param $userid 
     * @param $hashId
     * @param $typeToSet
     * 
     *  $typeToSet  0 = Image gallery
     *              1 = Event
     * 
     */
    private function modifyHashType($userid, $hashId, $typeToSet) 
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
    
            $sql = "UPDATE hash SET Type = :type WHERE OwnerId = :userid AND Id = :hashid";
            
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":userid", $userid);
            $stmt->bindParam(":type", $typeToSet);
            $stmt->bindParam(":hashid", $hashId);
            
            $stmt->execute();
        } catch (Exception $e) {  
            $this->throwException(DATABASE_ERROR, $e);
        }
        return true;
    }

    /***
     * EVENTS
     * 
     */
    
    /**
     * makeEventFromHash
     * 
     * @param $userid
     * @param $hashId
     * @return throw error or true
     */
    public function makeEventFromHash($userid, $hashId)
    {
        if( !$this->modifyHashType($userid, $hashId, QR_CODE_TYPE_EVENT) ) {
            return false;
        }
        return true;
    }
    
    /**
     * getEvents
     * 
     * @param unknown $userid
     * @return boolean|hashlist
     */
    public function getEvents($userid) 
    {
        if ( !$this->getUnusedHash($userid, QR_CODE_TYPE_EVENT) ) {
            return false;
        }
        if ( !empty($this->unusedHash) ) {
            return $this->unusedHash;
        }
        return false;
    }
    
    /**
     * Updates event information from current
     */
    public function updateEvent($userid) 
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            $type           = QR_CODE_TYPE_EVENT;
            $event_id       = $this->getEventId();
            $event_code     = $this->getEventCode();
            $event_name     = $this->getEventName();
            $event_descr    = $this->getEventDescr();
            
            $sql = "UPDATE hash SET Name = :name, Descr = :descr WHERE OwnerId = :userid AND Id = :eventid AND Hash = :hash AND Type = :type";
            
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(":userid",     $userid);
            $stmt->bindParam(":eventid",    $event_id);
            $stmt->bindParam(":type",       $type);
            $stmt->bindParam(":hash",       $event_code);
            $stmt->bindParam(":name",       $event_name);
            $stmt->bindParam(":descr",      $event_descr);
            
            $stmt->execute();
            
            // $stmt->debugDumpParams(); //debug
            
        } catch (Exception $e) {
            $this->throwException(DATABASE_ERROR, $e);
        }
        return true;
    }
    
    /**
     * getEventCodesFromDb
     * 
     * 
     * 
     * @param $userid
     * @return boolean
     */
    public function getEventCodesFromDb($userid) 
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            $event_id = $this->getEventId();
            
            $sql = "SELECT Id, Hash FROM hash WHERE FatherId = :id AND OwnerId = :userid";
            
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(":userid", $userid);
            $stmt->bindParam(":id",     $event_id);
            
            $stmt->execute();
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->usedHash['hash'][$row['Id']]['id'] = $row['Id'];
                $this->usedHash['hash'][$row['Id']]['hash'] = $row['Hash'];
                
            }
            return true;
            
        } catch (Exception $e) {
            $this->throwException(DATABASE_ERROR, $e);
        }
        return false;
    }
    
    /**
     * setEventId
     * 
     * @param $value
     */
    public function setEventId($value) 
    {
        $this->event_id = $value;
    }
    
    /**
     * getEventID
     * 
     * @return eventid
     */
    public function getEventId()
    {
        return $this->event_id;
    }
    
    /**
     * setEventCode
     * 
     * @param $value
     */
    public function setEventCode($value)
    {
        $this->event_code = $value;
    }
    
    /**
     * getEventCode
     * 
     * @return event_code
     */
    public function getEventCode()
    {
        return $this->event_code;
    }
    
    /**
     * setEventName
     * 
     * @param name $value 
     */
    public function setEventName($value)
    {
        $this->event_name = $value;
    }
    
    /**
     * getEventName
     * 
     * @return event_name
     */
    public function getEventName()
    {
        return $this->event_name;
    }

    /**
     * setEventDescr
     * 
     * @param event_descr $value
     */
    public function setEventDescr($value)
    {
        $this->event_descr = $value;
    }
    
    /**
     * getEventDescr
     * 
     * @return event_descr
     */
    public function getEventDescr()
    {
        return $this->event_descr;
    }

    /**
     * getImages
     * 
     * @return image[list]|boolean
     */
    public function getImages()
    {
        
        if ( !empty($this->images) ) {
            return $this->images;
        }
        return false;
    }
    
    /**
     * checkGalleryAvailability
     * 
     * 
     * 
     * @return boolean
     */
    public function checkGalleryAvailability()
    {
        try {
            $db = new Database();
            $this->database = $db->connect();
            
            $hash = $this->getEventCode();
            
            $sql = "SELECT h.Type FROM hash h WHERE h.Hash = :hash AND h.Disabled = 0";
            $stmt = $this->database->prepare($sql);
            $stmt->bindParam(":hash", $hash);
            
            $stmt->execute();

            // count rows
            $count = $stmt->rowCount();
            
            // if at least one match return true
            if ( $count > 0 ) {
                return true;
            }
            return false;   
        } catch (Exception $e) {
            $this->throwException(DATABASE_ERROR, $e);
        }
        return false;
    }
    
    /**
     * getGallery
     * 
     * getGallery for event_code
     * 
     * @return boolean
     */
    public function getGallery() 
    {

        try {
            $db = new Database();
            $this->database = $db->connect();
            
            $hash = $this->getEventCode();
            
            // first get type of hash. Is it event or regular gallery
            $sql = "SELECT Type, Name, Descr FROM hash WHERE Disabled = 0 AND Hash = :hash";
            
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(":hash", $hash);
            
            $stmt->execute();
            
            $type = $stmt->fetch(PDO::FETCH_ASSOC);
            
            
            // if type is 1 = event get all qr-code images which belongs to an event. Otherwise just get one gallery. 
            if ( isset($type) ) {
                if ( $type['Type'] == 1 ) {
                    $sql = "SELECT DISTINCT i.Id, i.HashId, ha.Hash, i.NameOnDisk FROM hash h, hash ha, images i WHERE h.Id = ha.FatherId AND h.Hash = :hash AND i.HashId = ha.Id AND i.Deleted = 0";
                } else {
                    $sql = "SELECT i.Id, i.HashId, h.Hash, i.NameOnDisk FROM images i, hash h WHERE h.Hash = :hash AND i.HashId = h.Id AND i.Deleted = 0";
                }
            }
            
            $stmt = $this->database->prepare($sql);
            
            $stmt->bindParam(":hash", $hash);
            
            $stmt->execute();
            
            //$stmt->debugDumpParams(); //debug

            //if ( $type['Type'] == 1) {
                $this->images['event']['type'] = $type['Type'];
                $this->images['event']['name'] = $type['Name'];
                $this->images['event']['descr'] = $type['Descr'];
            //}
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->images['image'][$row['Id']]['id'] = $row['Id'];
                $this->images['image'][$row['Id']]['hash'] = $row['Hash'];
                $this->images['image'][$row['Id']]['thumbnail'] = IMG_PATH.IMG_SUBPATH_THUMBNAIL.$row['NameOnDisk'];
                $this->images['image'][$row['Id']]['medium'] = IMG_PATH.IMG_SUBPATH_MEDIUM.$row['NameOnDisk'];
                $this->images['image'][$row['Id']]['fullsize'] = IMG_PATH.IMG_SUBPATH_FULL_SIZE.$row['NameOnDisk'];
                $this->images['image'][$row['Id']]['original'] = IMG_PATH.IMG_SUBPATH_ORIGINAL.$row['NameOnDisk'];
            }
            
            // if Event get images that are directly assigned to event. (No code)
            if ( $type['Type'] == 1 ) {
                $sql = "SELECT i.Id, i.HashId, h.Hash, i.NameOnDisk FROM images i, hash h WHERE h.Hash = :hash AND i.HashId = h.Id AND i.Deleted = 0";
                $stmt = $this->database->prepare($sql);
                
                $stmt->bindParam(":hash", $hash);
                
                $stmt->execute();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $this->images['image'][$row['Id']]['id'] = $row['Id'];
                    $this->images['image'][$row['Id']]['hash'] = $row['Hash'];
                    $this->images['image'][$row['Id']]['thumbnail'] = IMG_PATH.IMG_SUBPATH_THUMBNAIL.$row['NameOnDisk'];
                    $this->images['image'][$row['Id']]['medium'] = IMG_PATH.IMG_SUBPATH_MEDIUM.$row['NameOnDisk'];
                    $this->images['image'][$row['Id']]['fullsize'] = IMG_PATH.IMG_SUBPATH_FULL_SIZE.$row['NameOnDisk'];
                    $this->images['image'][$row['Id']]['original'] = IMG_PATH.IMG_SUBPATH_ORIGINAL.$row['NameOnDisk'];
                }
            }
            
            //print_r($this->images);
            if ( !empty($this->images) ) { 
                return true;
            }
            return false;
            
        } catch (Exception $e) {
            $this->throwException(DATABASE_ERROR, $e);
        }
        return true;
    }
    
    
    /***
     * OTHER
     *
     */
    
    /**
     * cmpImgTime
     * 
     * compares times which is greater
     * 
     * return   0 = equal
     *          1 = left is greater
     *         -1 = rigth is greater
     *  
     */
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

//$time_start = microtime(true);
//$koodit = new qr();
//$koodit->performanceTest();
//echo ($koodit->qrText);
//$koodit->generateQrCodes();
//print_r($koodit->$qrHash);

/*
$koodit->processImages();
*/

//$time_end = microtime(true);
//$execution_time = ($time_end - $time_start);
//echo 'Total Execution Time: '.$execution_time.' seconds<br>';



?>