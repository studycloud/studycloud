$(document).ready(function(){ 

	// Show/hide login dialog if you click on the log in button. 
	$("#log-in-button").click(function(event){ 
		document.getElementById('log-in-hidden').classList.toggle('swing-in-top-bck');
		document.getElementById('log-in-hidden').classList.toggle('swing-out-top-bck');
	});

});
