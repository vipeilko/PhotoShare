<?php
?>
<h1 id="contentheader">Eventmanagement</h1>
    		<div class="subitem content">
    			<div class="subitem secondsubitem currentusers">
    				<span class="subitemheader">Events</span>
    				<select class="list" name="eventlist" id="eventlist" size="7">
						
    				</select>
    				<input id="submitViewSelectedEvent" type="submit" value="view selected event">
    			</div>
    			
    			<div class="subitem secondsubitem currentusers">
    				<span class="subitemheader">Edit events</span>
    				<span>Code</span>
    				<input id="eventCode" type="text" disabled>

    				<span>Name</span>
    				<input id="eventName" type="text">
    				
    				<span>Description</span>
    				<input id="eventDescr" type="text">
    				
    				<input id="submitEditEvent" type="submit" value="Edit event">
    			</div>
    			
    			<div class="subitem secondsubitem currentusers">
    				<span class="subitemheader">Tools</span>
    				<input id="submitStartProcessingEvent" type="submit" value="start processing to this event">
    				
    			</div>
    			
    		</div> <!-- end of content -->
    		<div class="subitem content">
    		   	<div class="subitem secondsubitem currentusers">
    				<span class="subitemheader">Codes tied to event</span>
    				<select class="list" name="codelist" id="usedcodelist" size="7">
						
    				</select>
    				<input id="submitViewSelectedCode" type="submit" value="view selected code">
    			</div>
    		</div>
    		