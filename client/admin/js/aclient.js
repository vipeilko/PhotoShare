
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
	$('#loading').hide();
	$('#confirmation').dialog({
		resizable: false,
		autoOpen: false,
		modal: true,
		show : {
			effect : "bounce",
			duration : 500
		}
	});
	
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
	if ( $("#event").length ) {
		$("#event").click(function () { loadpage('events.php') });	
	}	
	if ( $("#code").length ) {
		$("#code").click(function () { loadpage('code.php') });	
	}
});

/**
 * ajaxStart
 * 
 * @returns
 */
$(document).ajaxStart(function () {
	$('#loading').show();
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
	$('#loading').hide();
	//console.log("ajaxStop code: " + JSON.stringify(code)); //debug
	switch (code) {
		case 200:
			
			break;
		case 201:
			//refreshtoken updated, let's do what we meant to do
			postToApi(dataToPostAfterRefreshingTokens);			
			break;
			
		case 221:
			// Get used codes
			
			break;
		case 222:
			// Get unused codes
			
			break;
		case 223:
			// Generate codes
			// refresh list after new codes are generated
			getUnusedCodes();
			break;
		case 224:
			// Clear unused codes
			// refresh list after codes are cleared
			getUnusedCodes();
			break;
		case 225:
			// pdf creation finnished
			break;
		case 240:
			// Event successfully generated
			// refresh list after 
			getUnusedCodes();
			break;
			
		case 242:
			// Edit event success
			// refresh list
			getEvents();
			
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
			console.log("Service name: " + dataToPost.serviceName); // debug servicename
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
			
			console.log("Response:" + str); //debug response
			
			//determine which type is response/error/warning
			if (data.hasOwnProperty('response') ) {
				code = data.response.status;
				//if ( data.response.message == null ) return;
				if ( !(data.response.message == null) ) {
				
				
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
							
							//console.log(subperm);
						}
						//console.log(data.response.message.users[user].firstname);
						//$('#userlist').append('<option id="user' + userdata.response.message.users[user].id + '" value="' + userdata.response.message.users[user].id + '">' + userdata.response.message.users[user].firstname + ' ' + userdata.response.message.users[user].lastname + '</option>');
					
					}
					return;
				}
				
				// Admin interface "dynamic" menu
				// Only when roles
				// if ( data.hasOwnProperty('role') ) {
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
					return;
				}
				
				// getUsers
				if ( data.response.message.hasOwnProperty('users') ) {
					//pass data to handler
					getUsersResponse(data);
					return;
				}
				
			} // END IF NULL
				
			} else if (data.hasOwnProperty('error')) {
				code = data.error.status;
			} else if (data.hasOwnProperty('warning')) {
				code = data.warning.status;
			} else {
				//there is no understandable reply
			}
			
			console.log("CODE: " + code);
			
			//lets read response
			switch (code) {
				 
				case 200:
					// handle ok responses here
					// a new switch case?
					
				
					break;
				case 201:
					// refresh token updated
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
				case 221:
					// Used codes list
					parseCodeListResponse(data, 'used');
					
					break;
				case 222:
					// Unused codes list
					parseCodeListResponse(data, 'unused');
					
					break;
				case 225: 
					// pdf generation complete
					// opens pdf-file to new tab
				    window.open(data.response.message);
					
					break;
				case 241:
					// event code list
					parseCodeListResponse(data, 'event');

					break;
				case 243:
					// used event codes, same handler as normal used codes
					parseCodeListResponse(data, 'used');
					
					break;
				case 302:
					// AccessToken: Experied token
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
function getUsers() {
	dataToPost = 
	  {
				"serviceName":"getUsers",
				"param":{
					"parametri":"juu"
				}
	  };
	  postToApi(dataToPost);
}
/**
 * parseCodeListResponse
 * 
 * @param data response data
 * @param type list type
 * 
 * parses response and adds data to elements
 * 
 */
function parseCodeListResponse(data, type) 
{
	if ( type == 'used') 
		$('#usedcodelist').html('');
	if ( type == 'unused') 
		$('#unusedcodelist').html('');
	if ( type == 'event') 
		$('#eventlist').html('');
	if (data.response.message != null ) {
		for (let hash of Object.keys(data.response.message.hash)) {
			// console.log(hash); //debug
			if ( type == 'used') 
				$('#usedcodelist').append('<option id="code_'+ data.response.message.hash[hash].hash +'" value="' + data.response.message.hash[hash].id + '">' + data.response.message.hash[hash].hash + '</option>');
			if ( type == 'unused') 
				$('#unusedcodelist').append('<option id="code_'+ data.response.message.hash[hash].hash +'" value="' + data.response.message.hash[hash].id + '">' + data.response.message.hash[hash].hash + '</option>');
			if ( type == 'event' ) {
				$('#eventlist').append('<option id="code_'+ data.response.message.hash[hash].hash +'" value="' + data.response.message.hash[hash].id + '">' + data.response.message.hash[hash].hash + ' - ' + data.response.message.hash[hash].name + '</option>');
				// add events for eventlist, values to input boxes
				$('#eventlist').on('click', function () {
					$('input#eventCode').val(data.response.message.hash[$('#eventlist').children("option:selected").val()].hash);
					$('input#eventName').val(data.response.message.hash[$('#eventlist').children("option:selected").val()].name);
					$('input#eventDescr').val(data.response.message.hash[$('#eventlist').children("option:selected").val()].descr);
				});
			}
		}
	}
}

/**
 * getUsersResponse
 * 
 * @param data
 * @returns
 */
function getUsersResponse(data) 
{
	//save userdata
	userdata = data;
	// load users to list
	// first empty list and set defaults
	$('#userlist').html('<option value="0">add user</option><option disabled>───────────────</option>');
	for (let user of Object.keys(userdata.response.message.users)) {
		//console.log(user);
		//console.log(data.response.message.users[user].firstname);
		$('#userlist').append('<option id="user' + userdata.response.message.users[user].id + '" value="' + userdata.response.message.users[user].id + '">' + userdata.response.message.users[user].firstname + ' ' + userdata.response.message.users[user].lastname + '</option>');
	
	}

	let clickCount = 0; // counts click on userlist to toggle edit/create button
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

			// get permissions
			getPermissionByUserId($('#userlist').children("option:selected").val());

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

/**
 * 
 * @param id
 * @returns
 */
function getPermissionByUserId(id) {
	// generate payload to request user permissions from API
	dataToPost = 
	{
			"serviceName":"getUserPermById",
			"param":{
				"userid":id
			}
	};
	// post permission request to API
	postToApi(dataToPost);
}

/**
 * 
 * @param id
 * @returns
 */
function deleteUser(id) {
	$('#confirmation').html("Are you sure to delete selected user?");
	$('#confirmation').dialog( {
		title : "Confirm delete user",
		buttons : {
			"Delete" : function() {
				let data = 
				{
						"serviceName":"deleteUser",
						"param":{
							"userid":id
						}
				};
				postToApi(data);
				$(this).dialog("close");
				// let deletion request complete before refreshing users list
				// there is probably better ways to do this, but this is simple
				setTimeout(getUsers, 1000);
			},
			"Cancel" : function() {
				$(this).dialog("close");
			}
		}
	})
	$("#confirmation").dialog("open");
	
}


function collectPermsAndPostToApi(sendPerm) {
	let tempPerm;
	let tempObj = {};
	
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
	
	//console.log(JSON.stringify(sendPerm)); //debug
	postToApi(sendPerm);
}
/**
 * 
 * @returns
 */
function addUser() {
	$('#confirmation').html("Do you want to create a new user?");
	$('#confirmation').dialog( {
		title : "Confirm creating new user",
		buttons : {
			"Create" : function() {
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
	collectPermsAndPostToApi(sendPerm);
	$(this).dialog("close");
	// let add request complete before refreshing users list
	// there is probably better ways to do this, but this is simple
	setTimeout(getUsers, 1000);
			},
			"Cancel" : function() {
				$(this).dialog("close");
			}
		}
	})
	$("#confirmation").dialog("open");
	getUsers();
}

/**
 * 
 * @returns
 */
function editUser() {

	let request = {
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
	collectPermsAndPostToApi(request);
	
}

function getUnusedCodes() {
	let request = {
			"serviceName":"getUnusedCodes",
			"param":{
				"start": 0,
				"end": 100
			}
	}
	postToApi(request);
}

function getUsedCodes() {
	let request = {
			"serviceName":"getUsedCodes",
			"param":{
				"start": 0,
				"end": 100
			}
	}
	postToApi(request);
}

function generateCodes(number) {
	let request = {
			"serviceName":"generateCodes",
			"param":{
				"ammount": number
			}
	}
	postToApi(request);
}

function clearUnusedCodes(number) {
	let request = {
			"serviceName":"clearUnusedCodes",
			"param":{
				"disabled": number
			}
	}
	postToApi(request);	
}

function printUnusedCodes() {
	let request = {
			"serviceName":"printUnusedCodes",
			"param":{
				"disabled": 0
			}
	}
	postToApi(request);	
}

function makeEvent(hashid) {
	let request = {
			"serviceName":"eventFromHashId",
			"param":{
				"hashid": hashid
			}
	}
	postToApi(request);	
}

function getEvents()
{
	
	let request = {
			"serviceName":"getEventList",
			"param":{
				"param": "param"
			}
	}
	postToApi(request);	
}


function editEvent() {
	let request = {
  				"serviceName":"editEvent",
  				"param":{
  					"id":$('#eventlist').children("option:selected").val(),
  					"code":$('#eventCode').val(),
  					"name":$('#eventName').val(),
  					"descr":$('#eventDescr').val()
  				}
		};
	postToApi(request);
}


function getCodesTiedToEvent () 
{
	let request = {
				"serviceName":"getEventCodes",
				"param":{
					"id":$('#eventlist').children("option:selected").val(),
				}
	};
postToApi(request);
}

/**
 * loadpage loads new content from template, api and defines all needed events on pages
 * 
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
	      
	      switch (page) {
	    	  case 'users.php':
	    		  $('#submitEditUser').click(function() { editUser() });
		    	  $('#submitCreateUser').click(function() { addUser() });
		    	  $('#submitRemoveUser').click(function() { deleteUser($('#userlist').children("option:selected").val())} );
		    	  
		    	  $('#userlist').children("option:selected").val();
		    	  
		    	  getUsers();
	    		  break;
	    	  case 'code.php':
	    		  getUsedCodes();
	    		  getUnusedCodes();
	    		  $('#submitGenerate').click(function() { generateCodes($('#numberOfNewCodes').val()) });
	    		  $('#submitClearUnused').click(function() { clearUnusedCodes(0) });
	    		  $('#submitPrintUnusedCodes').click(function() { printUnusedCodes() });
	    		  $('#submitMakeEvent').click(function() { makeEvent($('#unusedcodelist').children("option:selected").val()) });
	    		  
	    		  break;
	    	  case 'events.php':
	    		  getEvents();
	    		  
	    		  $('#submitEditEvent').click(function() { editEvent(); });
	    		  
				// add events for eventlist
				$('#eventlist').on('click', function () {
					getCodesTiedToEvent();
				});
	    		  
	    		  break;
	    	 default:
	    		 break;
	      }
	      
	    }
	  });
	}