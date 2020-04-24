<?php
?>

<h1 id="contentheader">MANAGE QR-CODES</h1>
    		<div class="subitem content">
    			<div class="subitem secondsubitem currentusers">
    				<p>Used codes</p>
    				<select class="list" name="codelist" id="usedcodelist" size="21">
						
    				</select>
    				<input id="submitViewSelected" type="submit" value="view selected">
    			</div>
    			
    			<div class="subitem secondsubitem currentusers">
    				<p>Unused codes</p>
    				<select class="list" name="codelist" id="unusedcodelist" size="21">
						
    				</select>

    			</div>
    			
    			<div class="subitem secondsubitem currentusers">
    				<p>Tools</p>
    				<input id="submitStartProcessing" type="submit" value="start processing">
    				<input id="submitClearUnused" type="submit" value="clear unused codes">
    				
    				
    				<input id="submitPrintUnusedCodes" type="submit" value="print unused codes">
    				
    				<span>Generate codes</span>
    				<input id="numberOfNewCodes" type="number">
    				<input id="submitGenerate" type="submit" value="generate codes">
    			</div>
    			
    		</div> <!-- end of content -->