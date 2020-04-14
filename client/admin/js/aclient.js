
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
var userdata = null;									// Stores userdata
var userpermissions = null;								// Stores userpermissions

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
		$("#user").click(function () { loadpage('users.php') });
	}
	if ( $("#main").length ) {
		$("#main").click(function () { loadpage('main.php') });	
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
				if ( data.response.message == null ) return;
				// getUserPermById
				if ( data.response.message.hasOwnProperty('permissions') ) {
					userpermissions = data;
					$("#permissions").html("");
					for (let perm of Object.keys(userpermissions.response.message.permissions) ) {
						//console.log(perm); //debug
						for  ( let subperm of Object.keys(userpermissions.response.message.permissions[perm] ) ) {
							if ( (subperm.localeCompare("label") == 0)  ) {
								
								$('#permissions').append('<span id="' + perm + '_' + subperm + '_' + userpermissions.response.message.permissions[perm][subperm].permid + '" class="subitemheader">' + userpermissions.response.message.permissions[perm][subperm].label + '</span>');
							
							} else {
								if ( userpermissions.response.message.permissions[perm][subperm].authorized == 1 ) {
									$('#permissions').append('<label><input id="' + perm + '_' + subperm + '_' + userpermissions.response.message.permissions[perm][subperm].permid + '" type="checkbox" checked><span>' + userpermissions.response.message.permissions[perm][subperm].label + '</span></label>');
								} else {
									$('#permissions').append('<label><input id="' + perm + '_' + subperm + '_' + userpermissions.response.message.permissions[perm][subperm].permid + '" type="checkbox"><span>' + userpermissions.response.message.permissions[perm][subperm].label + '</span></label>');
								}
								
							}
							
							console.log(subperm);
						}
						//console.log(data.response.message.users[user].firstname);
						//$('#userlist').append('<option id="user' + userdata.response.message.users[user].id + '" value="' + userdata.response.message.users[user].id + '">' + userdata.response.message.users[user].firstname + ' ' + userdata.response.message.users[user].lastname + '</option>');
					
					}
				}
				
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
					//save userdata
					userdata = data;
					// load users to list
					for (let user of Object.keys(userdata.response.message.users)) {
						//console.log(user);
						//console.log(data.response.message.users[user].firstname);
						$('#userlist').append('<option id="user' + userdata.response.message.users[user].id + '" value="' + userdata.response.message.users[user].id + '">' + userdata.response.message.users[user].firstname + ' ' + userdata.response.message.users[user].lastname + '</option>');
					
					}

					let clickCount = 0;
					$('#userlist').on('click', function () {
						
						if ( $('#userlist').children("option:selected").val() != 0 ) {
							clickCount++;
							
							// change button
							if ( clickCount == 1) {
								$('#submitCreateUser').toggle();
								$('#submitEditUser').toggle();
							}
							
							// Fill form with selected user information
							$('input#firstname').val(userdata.response.message.users[$('#userlist').children("option:selected").val()].firstname);
							$('input#lastname').val(userdata.response.message.users[$('#userlist').children("option:selected").val()].lastname);
							$('input#email').val(userdata.response.message.users[$('#userlist').children("option:selected").val()].email);

							// generate payload to request user permissions from API
							dataToPost = 
							{
									"serviceName":"getUserPermById",
									"param":{
										"userid":$('#userlist').children("option:selected").val()
									}
							};
							// post permission request to API
							postToApi(dataToPost);
						} else {
							
							// if first of the list 'add user' is selected value is 0
							// clear all input fields with type text
							$('.edituser input:text').val("");
							// clear checkbox selections
							$('.edituser input:checkbox').attr('checked', false);
							
							if( clickCount != 0 ) {
								$('#submitCreateUser').toggle();
								$('#submitEditUser').toggle();
							}
							clickCount = 0;
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
						console.log(code + " : " + this.id);
						if ( value.length == 0 || this.id == "email" && code == 112 || this.id == "textpassword" && code == 113) {
							console.log("Highlight");
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
 * @returns
 */

//TODO: continue from here
function addUser() {
	let tempPerm;
	let tempObj = {};
	let sendPerm = {
  				"serviceName":"addUser",
  				"param":{
  					"userid":$('#userlist').children("option:selected").val(),
  					"firstname":$('#firstname').val(),
  					"lastname":$('#lastname').val(),
  					"email":$('#email').val(),
  					"password":$('#password').val(),
  					"retypepassword":$('#retypepassword').val()
  				}
		};		
	
	$('#permissions input').each(function() {
		if (this.id != "" ) {
			if ( this.checked ) {
				tempPerm = this.id.split("_", 3);
				let header = tempPerm[0];
				let descr = tempPerm[1];
	//			console.log("Header: " + header + " descr: " + descr);
				tempObj = 
				{
					"param":{
						"permissions": {
							[header]: {
				                   [descr]: {
				                       "permid": tempPerm[2],
				                       "authorized": "1"
				                   }
				            }
						}
					}
				};

				$.extend(true, sendPerm, tempObj, sendPerm);
				//sendPerm = tempObj2;
			} //END IF ELSE HERE
		}

	});
	
	console.log(JSON.stringify(sendPerm));
	postToApi(sendPerm);
}

/**
 * 
 * @returns
 */
function editUser() {
	let tempPerm;
	let tempObj = {};
	/*let sendPerm = { 
			permissions:[{}]
	};*/
	let sendPerm = {
  				"serviceName":"editUser",
  				"param":{
  					"userid":$('#userlist').children("option:selected").val(),
  					"firstname":$('#firstname').val(),
  					"lastname":$('#lastname').val(),
  					"email":$('#email').val(),
  					"password":$('#password').val(),
  					"retypepassword":$('#retypepassword').val()
  				}
		};		
	
	$('#permissions input').each(function() {
		if (this.id != "" ) {
			if ( this.checked ) {
				tempPerm = this.id.split("_", 3);
				let header = tempPerm[0];
				let descr = tempPerm[1];
	//			console.log("Header: " + header + " descr: " + descr);
				tempObj = 
				{
					"param":{
						"permissions": {
							[header]: {
				                   [descr]: {
				                       "permid": tempPerm[2],
				                       "authorized": "1"
				                   }
				            }
						}
					}
				};

				$.extend(true, sendPerm, tempObj, sendPerm);
				//sendPerm = tempObj2;
			} //END IF ELSE HERE
		}
		
	});
	
	console.log(JSON.stringify(sendPerm));
	postToApi(sendPerm);
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
	    	  $('#submitEditUser').click(function() { editUser() });
	    	  $('#submitCreateUser').click(function() { addUser() });
	    	  
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