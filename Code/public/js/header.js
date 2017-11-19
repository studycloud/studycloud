// Right, jQuery is a thing. 
$(document).ready(function(){ 

	$("#logInButton").click(function(event){ // Show/hide dialog if you click on the log in button. 
		event.stopPropagation();
		$("#logInHidden").toggle(1000);
	});

	// TODO: Real freakin buggy. I'll come back and either fix this or refactor and delete it. 
	// $("*:not(#logInHidden).find(*), *:not(#logInButton).find(*)").click(function(event){ // Show/hide dialog if you click on the log in button. 
	// 	event.stopPropagation();
	// 	$("#logInHidden").hide(1000);
	// });

});