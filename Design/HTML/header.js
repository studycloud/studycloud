// Right, jQuery is a thing. 
$(document).ready(function(){ 
	$("#logInButton").click(function(){ // Show/hide if you click on button. 
		$("#logInHidden").show(1000);
	});
	$("body").on("click", ":not(#logInButton, #logInHidden, #logInHidden *)", function(){ 
		// if ( $("#logInHidden").css('display') != 'none' ){
			$("#logInHidden").hide(1000); 
		// }
	});
});