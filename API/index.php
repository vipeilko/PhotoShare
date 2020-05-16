<?php

/**
 * PhotoShare API 
 * 
 * @author Ville Kouhia
 * @version 0.0.1
 * @date 
 * 
 */
    
    // requires composer autoload script for all third party components
    require_once('../vendor/autoload.php');
    
    //moved from rest.php. Better include here or move to functions.php
    require_once('settings.php');
    
    // requires all classes which are somewhere in project. <classname>.php 
    require_once('functions.php');
    
    $api = new Api();
    $api->processApi();


?>