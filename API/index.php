<?php

/**
 * PhotoShare API 
 * 
 * @author Ville Kouhia
 * @version 0.0.1
 * @date 
 * 
 */
    
    // requires composer autoload script for firebase JWT extension which is used to generate tokens
    require '../vendor/autoload.php';
    // requires all classes which are somewhere in project. <classname>.php 
    require_once('functions.php');
    $api = new Api();
    $api->processApi();


?>