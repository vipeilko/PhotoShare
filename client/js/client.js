/**
 * client.js
 * 
 * @author vipeilko
 * 
 * 23.3.2020 Introduction to handle login information and get response from API
 * 25.3.2020 Update response handling from api and redirecting when login succesfull. 
 * 			   Also changes links login->admin page & logout when tokens is stored.
 * 27.3.2020 Updated more useful post function
 * 
 */

//SETTINGS
// API URL
const apiUrl = 'http://192.168.1.4/PhotoShare/API/';	// Application program interface url
var code = null; 										// Stores latest response code from API
const NUMBER_OF_IMAGES_IN_ROW = 4;						// Defines how many images is displayed in a row in gallery

//END SETTINGS

$(document).ready(function() {
	console.log(sessionStorage.getItem('accessToken')); // debug
	console.log(sessionStorage.getItem('refreshToken')); // debug

	UrlExists(apiUrl, function(status){
	    if(status === 404){
	      $("body").html('<h1>Service not configured</h1>');
	    }
	});
	
	
	// if tokens is already issued display a bit different links
	if ( sessionStorage.getItem('accessToken') != null && sessionStorage.getItem('refreshToken') != null ) {
		//console.log("true"); //debug
		
		$("#navlogin, #navcode").parent().hide();
		$("#navadminpage, #navlogout").parent().show();
		//$(".tologinbutton").html('<a href="admin/">admin page</a><br><a href="#logout">logout</a>');
	} else {
		//console.log("false"); //debug
		$("#navlogin, #navcode").parent().show();
		$("#navadminpage, #navlogout").parent().hide();
	}
	
	//if #login form button is available add listener for doLogin
	if ($("#login").length) {
		$("#login").bind('click', doLogin);
	}
	if ($("#navlogout").length) {
		$("#navlogout").bind('click', doLogout);		
	}
	
	// highlight active selection on menu and remove previous
    $('nav li a').click(function(e) {
        $('nav li.active').removeClass('active');
        var $parent = $(this).parent();
        $parent.addClass('active');
    });
    
    // Triggers post to api when field is filled with 8 characters
    $('#code').change(function() {
    	// if code is not 8 lenght return immediately
    	if ( $(this).val().length != 8 ) {
    		return;
    	}
    	// if length is 8 we make request to api 
    	let data = 
    	{
    		"serviceName":"isGalleryAvailable",
    		"param":{
    			"code":$(this).val()
    		}
    	};
    	postToApi(data);
    });
    
    // Gallery trigger get images
    if ($('#albumcode').length > 0) {
	    if ( $('#albumcode').val().length == 8 ) {
	    	getGallery($('#albumcode').val());
	    }
    }
    //$('#albumcode').
    

}); //end document ready

function getGallery(value)
{
	let data = 
	{
		"serviceName":"getGallery",
		"param":{
			"code":value
		}
	};
	postToApi(data);
}
/*
 * 
 */
function validateRefreshToken() 
{
	let data = 
	{
		"serviceName":"validateRefreshToken",
		"param":{
			"parametri":"juu"
		}
	};
	
	postToApi(data);
	if (ans) {
		//if refreshtoken is still valid lets set new access and refreshtokens
		if (ans.response.message.accessToken && ans.response.message.refreshToken) {
			
			sessionStorage.setItem('accessToken', ans.response.message.accessToken);
			sessionStorage.setItem('refreshToken', ans.response.message.refreshToken);

		} else {
			//not recieving access and refrestokens
		}
	}
}

function UrlExists(url, cb){
    jQuery.ajax({
        url:      url,
        dataType: 'text',
        type:     'POST',
        complete:  function(xhr){
            if(typeof cb === 'function')
               cb.apply(this, [xhr.status]);
        }
    });
}

/**
 * ajaxStop
 * 
 * This runs after every ajax function is finished. For example handles redirection on logon.
 * So this helps with asyncronous responses
 * 
 * 
 * @returns
 */
$(document).ajaxStop(function() {
	switch (code) {
		case 200:
			//redirect to admin page
			window.location.replace('admin/index.php');
			break;
		case 201:
			//refreshtoken updated
			break;
		default:
			//do nothing
			break;
	}
	
});

/**
 * 
 * @param dataToPost
 * @returns
 */
function postToApi(dataToPost) {
	let headerType, authHeader;
	// if tokens is already available pass them in header
	if ( sessionStorage.getItem('accessToken') != null && sessionStorage.getItem('refreshToken') != null ) {
		let headerType = 'Authorization';
		let authHeader = 'Bearer ' + sessionStorage.getItem('accessToken');
		//console.log(headerType + ": " + authHeader); //debug
	}

	$.ajax({
		url: apiUrl,
		headers: {
			headerType:authHeader
		},
		type: 'post',
		contentType: 'application/json',
		data: JSON.stringify(dataToPost),
		success: function(data) {
			let str = JSON.stringify(data);
			console.log(str);
			
			//determine which type is response/error/warning
			if (data.hasOwnProperty('response')) {
				code = data.response.status;
			} else if (data.hasOwnProperty('error')) {
				code = data.error.status;
			} else if (data.hasOwnProperty('warning')) {
				code = data.warning.status;
			} else {
				//there is no understandable reply
			}
			
			//console.log(code);
			
			//lets read response
			switch (code) {
				// login
				case 200:
				// refresh token updated
				case 201:
					// if login credentials are ok and response 200, lets store given tokens
					// other case with code 200 is when we are refreshing tokens with refreshtoken, still same procedure 
						if (data.response.message.accessToken && data.response.message.refreshToken) {
							//TODO:how to keep sessions alive when opening new tab in browser?
							sessionStorage.setItem('accessToken', data.response.message.accessToken);
							sessionStorage.setItem('refreshToken', data.response.message.refreshToken);
							return true;
						} else {
							return false;
						}

					break;
				case 250:
					// Gallery obtained
					parseObtainedGalleryData(data);
					break;
				case 251:
					// Gallery is available
					
					if ( data.response.hasOwnProperty('message') ) {
						window.location.replace('album/' + data.response.message);
					}
					
					break;
				//field is empty but mandatory
				case 103:
				//username error
				case 112:
				//password error
				case 113:
				//highlight all empty fields for few seconds
					 $( "input" ).each( function(){
						let value = $(this).val();
						//console.log(this.id);
						if ( value.length == 0 || this.id == "textusername" && code == 112 || this.id == "textpassword" && code == 113) {
							let originalColor = $(this).css("background-color");
							//console.log(originalColor);
							$(this).animate({'backgroundColor': "#e49b9b"}, 1000, function() {
								$(this).animate({'backgroundColor': originalColor }, 1000);
							});
						}
					 });
					 
					break;
					
				//login failed incorrect password or email; shakes login button
				case 108:
					$(".loginsubmit").effect("shake");
					break;
					
				default:
					
					break;
				
			}
		
		}
	}) //end ajax
} //end postToApi

function parseObtainedGalleryData(data) 
{
	// First clear content
	$('#gallery').html('');
	
	// If event add header and description
	if ( data.response.message.hasOwnProperty('event') ) {
		if ( data.response.message.event.hasOwnProperty('type') ) {
			if ( data.response.message.event.type == 1 ) {
				$('#eventheader').text(data.response.message.event.name);
				$('#eventdescr').text(data.response.message.event.descr);			
			}
		}
	}
	
	// count images
	let noi = 0; // number of images
	if ( data.response.message.hasOwnProperty('image') ) {
		for (let id of Object.keys(data.response.message.image)) {
			noi++;
		}
	}
	// calculate how many images in vertical
	let imgv = noi / NUMBER_OF_IMAGES_IN_ROW; 
	let fullrows = Math.round(imgv);
	// console.log("Img v: " + imgv);
	// console.log("Full rows: " + fullrows);
	
	let i = 1;
	
	if ( data.response.message.hasOwnProperty('image') ) {
		for (let id of Object.keys(data.response.message.image)) {
			if ( i == 1 || i % fullrows == 0) {
				// console.log("i % fullrows: " + i % fullrows );
				// console.log("i: " + i );
				$('#gallery').append('<div class="column"></div>');
			}
			$('#gallery > div:last').append('<a target="_blank" href="'+data.response.message.image[id].original+'"><img src="'+data.response.message.image[id].medium+'" style="width: 98%"></a>');
			i++;
		}
	}
}

/**
 * 
 * @returns
 */
function doLogout() 
{
	sessionStorage.removeItem('accessToken');
	sessionStorage.removeItem('refreshToken');
	window.location.replace('');
}

/**
 * doLogin 
 * 
 * 
 * POSTs ajax login request to api and handles feedback
 * 
 */
function doLogin() 
{
	let username = $('#textusername').val();
	let password = $('#textpassword').val();
	
	let jsondata = {
		    "serviceName": "generateToken",
		    "param": {
		        "email": username,
		        "password": password
		    }	
	};
	postToApi(jsondata);
} //end doLogin
