
/**
 * aclient.js
 * 
 * 1.4.2020 First implementation
 * 5.4.2020 Handles now both tokens properly
 * 			Added handling of responses getUsers and roles
 * 
 */


// API URL
const apiUrl = 'http://192.168.1.4/PhotoShare/API/';	// Application program interface url
var code = null; 										// Stores latest response code from API
var type = null;										// Stores latest response type from API
var dataToPostAfterRefreshingTokens = null;				// Stores last request that failed because of experied token

//Document loaded
$(document).ready(function() {
	
	console.log(sessionStorage.getItem('accessToken')); // debug
	console.log(sessionStorage.getItem('refreshToken')); // debug

	
	// hide all from left and right
	$("#user, #code, #event").hide();
	$(".currentusers, .adduser, #contentheader").hide();
	
	// if tokens is already issued and saved lets load page
	if ( sessionStorage.getItem('accessToken') != null && sessionStorage.getItem('refreshToken') != null ) {
		// roles first and generate then generate menu 
		let postData = 
		{
				"serviceName":"getRoles",
				"param":{
					"parametri":"juu"
				}
		};
		postToApi(postData);
	} else {
		// if there is no tokens, no access and head back to public frontpage
		window.location = '../';
	}
	
	//initialize events
	if ($("#logout").length) {
		$("#logout").on('click', doLogout);		
	}

	if ( $("#user").length ) {
		$("#user").click(function () { loadpage('users.php')});
	}
	if ( $("#main").length ) {
		$("#main").click(function () { loadpage('main.php')});	
	}
	
});


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
	
	//console.log("ajaxStop code: " + JSON.stringify(code)); //debug
	switch (code) {
		case 200:
			
			break;
		case 201:
			//refreshtoken updated, let's do what we meant to do
			postToApi(dataToPostAfterRefreshingTokens);			
			break;
		case 666:
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
	let authHeader;
	// if tokens is already available pass them in header
	if ( sessionStorage.getItem('accessToken') != null && sessionStorage.getItem('refreshToken') != null ) {
		let headerType = 'Authorization';
		let temp = JSON.stringify(dataToPost);
		// if requested function is validateRefreshToken use refreshtoken instead
//		if ( (temp.search("validateRefreshToken")) )  {
		if ( dataToPost.hasOwnProperty('serviceName') )  {
			console.log("Service name: " + dataToPost.serviceName);
			if ( !(dataToPost.serviceName == "validateRefreshToken") ) {
			authHeader = 'Bearer ' + sessionStorage.getItem('accessToken');
			} else {
			authHeader = 'Bearer ' + sessionStorage.getItem('refreshToken');
		}
		//console.log(headerType + ": " + authHeader); //debug
	
		}
	}

	$.ajax({
		url: apiUrl,
		headers: {
			Authorization:authHeader
		},
		type: 'post',
		contentType: 'application/json',
		data: JSON.stringify(dataToPost),
		success: function(data) {
			let str = JSON.stringify(data);
			
			console.log(str); //debug response
			
			//determine which type is response/error/warning
			if (data.hasOwnProperty('response') ) {
				code = data.response.status;
		
				//Only when roles
				//if ( data.hasOwnProperty('role') ) {
				if ( data.response.message.hasOwnProperty('role') ) {
					if ( data.response.message.role.hasOwnProperty('user') ) {
						//show user
						$('#user').show();
						
					}
					if ( data.response.message.role.hasOwnProperty('code') ) {
						//show code
						$('#code').show();
					}
					if ( data.response.message.role.hasOwnProperty('event') ) {
						//show event
						$('#event').show();
					}
				}
				
				// getUsers
				if ( data.response.message.hasOwnProperty('users') ) {
					
					// load users to list
					for (let user of Object.keys(data.response.message.users)) {
						//console.log(user);
						//console.log(data.response.message.users[user].firstname);
						$('#userlist').append('<option id="user' + data.response.message.users[user].id + '" value="' + data.response.message.users[user].id + '">' + data.response.message.users[user].firstname + ' ' + data.response.message.users[user].lastname + '</option>');
					
					}
					
					//TODO: continue here
					$('#userlist').on('click', function () {
						if ( $('#userlist').children("option:selected").val() != 0 ) {	
							dataToPost = 
							{
									"serviceName":"getUserInformationById",
									"param":{
										"userid":$('#userlist').children("option:selected").val()
									}
							};
							postToApi(dataToPost);
						}
					});
				}
				
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
				 
				case 200:
					// handle ok responses here
					// a new switch case
					
				// refresh token updated
					break;
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
				// AccessToken: Experied token
				case 302:
					//Save data that has failed to post because experied token
					dataToPostAfterRefreshingTokens = dataToPost;
					//refrestoken 
					validateRefreshToken();
					break;
					
  			    // RefreshToke: Experied token
				case 303:
					doLogout();
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

/*
 * 
 */
function validateRefreshToken() {
	let data = 
	{
		"serviceName":"validateRefreshToken",
		"param":{
			"parametri":"juu"
		}
	};
	
	postToApi(data);
/*	if (ans) {
		//if refreshtoken is still valid lets set new access and refreshtokens
		if (ans.response.message.accessToken && ans.response.message.refreshToken) {
			
			sessionStorage.setItem('accessToken', ans.response.message.accessToken);
			sessionStorage.setItem('refreshToken', ans.response.message.refreshToken);

		} else {
			//not recieving access and refrestokens
		}
	}*/
}

/**
 * 
 * @returns
 */
function doLogout() {
	sessionStorage.removeItem('accessToken');
	sessionStorage.removeItem('refreshToken');
	window.location.replace('');
}

/**
 * 
 * @param page
 * @returns
 */
function loadpage(page){
	  $.ajax({
	    url:page,
	    beforeSend:function(){
	      $('#rightcontainer').html("loading content...");
	    },
	    success:function(data){
	      $('#rightcontainer').html("");
	      $('#rightcontainer').html(data);
	      
	      if( page == 'users.php' ) {
	    	  dataToPost = 
	    	  {
	    				"serviceName":"getUsers",
	    				"param":{
	    					"parametri":"juu"
	    				}
	    	  };
	    	  postToApi(dataToPost);
	    	  
	      }
	      
	    }
	  });
	}