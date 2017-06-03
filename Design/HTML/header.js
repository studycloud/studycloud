// Right, jQuery is a thing. 
$(document).ready(function(){ 

	$("#logInButton").click(function(event){ // Show/hide dialog if you click on the log in button. 
		event.stopPropagation();
		$("#logInHidden").toggle(1000);
	});

	$("*:not(#logInButton, #logInButton *)").click(function(event){ // Show/hide dialog if you click on the log in button. 
		event.stopPropagation();
		$("#logInHidden").hide(1000);
	});


	// $(document).keypress(function(event){ // Show/hide if you click on button. 
	// 	if(event.which == 27){
	// 		$("#logInHidden").hide(1000);
	// 	}
	// 	event.stopPropagation();
	// });

});