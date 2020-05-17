<?php
/**
 * client/index.php
 *
 * 06.03.2020 First introduction
 * 27.03.2020 First version ready
 * 16.05.2020 New layout; simplier and more like admin panel
 *            Fisrt introduction of code gallery
 * 
 *
 */

/*

// TODO: proper url rewrite, for now moved to album/index.php

$load = null;
// To handle between login screen and gallery/events
// remove last slash if available
$path = rtrim($_SERVER['REQUEST_URI'], '/');

$elements = explode('/', $path);

//echo(count($elements));
print_r($elements);
// if elements empty
if ( empty($elements[3]) ) {
    $load = 'login.php';
} else {
    switch($elements[3]) {
    case 'album':        
        $load = 'album.php';
        break;
    default:
        header('HTTP/1.1 404 Not Found');
        //exit("");
    }
}
*/
?>
<!DOCTYPE html>
<html>
<head>
    <title>PhotoShare</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="js/transition.js"></script>
    <script src="js/client.js"></script>
</head>

<body>

<div class="container">
	<div class="logo"><img src="content/logo.png" alt="logo"></div>

	<!-- dynamically generated content -->
	<?php 
	/*
	if ( !isset($load) ) {
	    exit("Sorry, Something went wrong...");
	}
	include($load);
	*/
	include 'login.php';
	?>


</div><!-- end of container -->

</body>

</html>
