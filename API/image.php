<?php
/**
 * To generate background images and logos etc.... 
 * + 06.03.2020 Introduction of generating random backgroundimage
 * 
 */

if ( $_GET['image'] == "photoshare.png" ) {
    header('Content-type: image/png');
    $png_image = imagecreate(1920, 1080);
    $white = imagecolorallocate($png_image, 255, 255, 255);
    $black = imagecolorallocate($png_image, 0,0,0);
    $colorPalette = array (
                            imagecolorallocate($png_image, 10, 11, 13),
                            imagecolorallocate($png_image, 37, 47, 57),
                            imagecolorallocate($png_image, 73, 71, 76),
                            imagecolorallocate($png_image, 175, 160, 149),
                            imagecolorallocate($png_image, 249, 229, 204)
        
    );
    imagefilltoborder($png_image, 0, 0, $white, $white);
    
    
    $k = rand(50,100);
    
    for ( $i = 0; $i <= $k; $i++ ) {
        $color = array_rand($colorPalette);
        $squareSize = rand(20,100);
        $x = rand(20,1900-$squareSize);
        $y = rand(20,1060-$squareSize);
        $x2 = $x+$squareSize;
        $y2 = $y+$squareSize;
        //border is 2px larger than square it self
        imagefilledrectangle ($png_image, ($x-2), ($y-2), ($x2+2), ($y2+2), $black);
        // square top of border
        imagefilledrectangle ($png_image, $x, $y, $x2, $y2, $color);
        
    }
    
    imagepng($png_image);
    imagedestroy($png_image);
}

if ( $_GET['image'] == "background.png" ) {
    header('Content-type: image/png');
    $png_image = imagecreate(1920, 1080);
    $white = imagecolorallocate($png_image, 255, 255, 255);
    $black = imagecolorallocate($png_image, 0,0,0);

    imagefilltoborder($png_image, 0, 0, $white, $white);
    
    $k = rand(50,100);
    
    for ( $i = 0; $i <= $k; $i++ ) {
        $color = imagecolorallocate($png_image, rand(50,200), rand(50,200), rand(50,200));
        $squareSize = rand(20,100);
        $x = rand(20,1900-$squareSize);
        $y = rand(20,1060-$squareSize);
        $x2 = $x+$squareSize;
        $y2 = $y+$squareSize;
        //border is 2px larger than square it self
        imagefilledrectangle ($png_image, ($x-2), ($y-2), ($x2+2), ($y2+2), $black);
        // square top of border
        imagefilledrectangle ($png_image, $x, $y, $x2, $y2, $color);  
        
    }
    
    imagepng($png_image);
    imagedestroy($png_image);
}

?>