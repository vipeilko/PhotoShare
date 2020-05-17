<?php
?>
<!-- navigation -->
<nav class="subitem navigation">
    <ul>
        <li class="active"><a id="navcode" href="#">enter code</a></li>
        <li><a id="navlogin" href="#">login</a></li>
        <li><a id="navadminpage" href="admin/">admin page</a></li>
        <li><a id="navlogout" href="#">logout</a></li>
	</ul>
</nav>
<!-- end of navigation -->
<div class="subcontainer">
    <span class="subitem">Searching for a photo? Enter your 8 digt code or use QR-reader</span>
    
    <div class="subitem code form-group">
        <span class="left">CODE</span>
        <input class="form-field" placeholder="C6DEFC9E" maxlength="8" type="text" id="code" name="code"><br>
    </div>
    <div class="subitem username form-group">
        <span class="left">USERNAME</span>
        <input class="form-field" placeholder="username" maxlength="20" type="text" id="textusername" name="username"><br>
    </div>
    <div class="subitem password form-group">
        <span class="left">PASSWORD</span>
        <input class="form-field" placeholder="password" maxlength="50" type="password" id="textpassword" name="username"><br>
    </div>
    <div class="subitem submit loginsubmit form-group">
    	<input class="form-field" type="submit" value="Login" id="login" name="login"><br>
    </div>

</div> <!-- end of subcontainer -->
