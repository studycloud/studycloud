$(document).ready(function(){ 

	// When the user clicks on the register button, open the modal but with the register content
	$("#register-btn").click(function(event) {
	    document.getElementById('my-modal').style.display = "block";
	    document.getElementById('register-content').style.display = "block";
	    document.getElementById('forget-content').style.display = "none";	    
	});

	// When the user clicks on the forget button, also open the modal but with the forget content
	$("#forget-btn").click(function(event) {
	    document.getElementById('my-modal').style.display = "block";
	    document.getElementById('forget-content').style.display = "block";
	    document.getElementById('register-content').style.display = "none";
	});

	// When the user clicks on <span> (x), close the modal
	$("#close-modal").click(function(event) { // TODO: MAKE THIS LESS GENERAL
	    document.getElementById('my-modal').style.display = "none";
	});

});

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == document.getElementById('my-modal')) {
        document.getElementById('my-modal').style.display = "none";
    }
}