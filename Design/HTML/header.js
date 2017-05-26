// Right, jQuery is a thing. 
$(document).ready(function(){ 

	$("#logInButton").click(function(){ // Show/hide if you click on button. 
		$("#logInHidden").show(1000);
	});

	$("body").on("click", ":not(#logInButton, #logInHidden, #logInHidden *)", function(){ 
		if ( $("#logInHidden").css('display') != 'none' ){
			$("#logInHidden").hide(1000); 
		}
	});

});


// Attempting to make dialog hide when you click outside. Currently, shows and immediately hides. I think click events aren't separate. 
// if this doesn't work change the show in line 5 to a toggle and take out lines 8-12 and everything should be fine. 