$(document).ready(function(){ 

	// Show/hide login dialog if you click on the log in button. 
	$("#logInButton").click(function(event){ 
		document.getElementById('logInHidden').classList.toggle('swing-in-top-bck');
		document.getElementById('logInHidden').classList.toggle('swing-out-top-bck');
	});

});
