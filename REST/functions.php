<?php
    
spl_autoload_register(function($className) {
   
    
    $path = strtolower($className) . ".php";
    //Next line are used for debuggin which files are imported to a project
    //echo $path . "\n";
    
    if ( file_exists($path)) {
        require_once($path);
    }else {
        echo ("File $path is not found.");
    }
})

?>