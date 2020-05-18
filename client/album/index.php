<?php
/**
 * PhotoShare/client/album/index.php
 * 
 * 17.05.2020 Introduction of album indexer
 * 
 */

// To handle between login screen and gallery/events
// remove first and last slash if available
$path = rtrim($_SERVER['REQUEST_URI'], '/');
$path = ltrim($path, '/');

$elements = explode('/', $path);

// print_r($elements); // debug

// if path is .../album/<qr-code> set value and process it with js
if ( isset($elements[3] )) {
    $value = $elements[3];
} else {
    // if it is empty head back to main page
    header("Location: ../" );
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>PhotoShare - album</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
	<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="../js/transition.js"></script>
    <script src="../js/client.js"></script>
</head>

<body>

<div class="container">
	<div class="logo"><img src="../content/logo.png" alt="logo"></div>
	
        <div class="subcontainer">
            <!-- navigation -->
            <nav class="subitem navigation">
                <ul>
                    <li class="active"><a id="navcode" href="#"><?php if ( isset($value) ) echo($value);?></a></li>
                    <li><a id="navlogin" href="../">main</a></li>
                    <li><a id="navadminpage" href="../admin/">admin page</a></li>
                    <li><a id="navlogout" href="#">logout</a></li>
            	</ul>
            </nav>
            <!-- end of navigation -->
            <input class="hidden" type="hidden" id="albumcode" name="code" value="<?php if ( isset($value) ) echo($value);?>">
        	<h2 id="eventheader"></h2>
        	<p id="eventdescr"></p>
        <div id="gallery">
        	
        
        </div><!-- end of #gallery -->
        
        </div> <!-- end of subcontainer -->
		


</div><!-- end of container -->

</body>

</html>