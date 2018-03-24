$(document).ready(function(){ 

	// When the user clicks on the button, open the modal 
	$("#myBtn").click(function(event) {
	    document.getElementById('myModal').style.display = "block";
	});

	// When the user clicks on <span> (x), close the modal
	$("#closeModal").click(function(event) { // TODO: MAKE THIS LESS GENERAL
	    document.getElementById('myModal').style.display = "none";
	});

});

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}