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
    require_once('../vendor/autoload.php');
    
    //moved from rest.php. Better include here or move to functions.php
    require_once('settings.php');
    
    // requires all classes which are somewhere in project. <classname>.php 
    require_once('functions.php');
    
    // TODO: need to solve how this ApiProcess should execute. Propably change __construct a littlebit
    //       There is overlapping functions
    $api = new Api();
    $api->processApi();


?>