$(document).ready(function() {
  // When the user clicks on the register button, open the modal but with the register content
  $("#register-btn").click(function(event) {
    document.getElementById("my-modal").style.display = "block";
    displayContainer("register");
  });

  // When the user clicks on the forget button, also open the modal but with the forget content
  $("#forget-btn").click(function(event) {
    document.getElementById("my-modal").style.display = "block";
    displayContainer("forget");
  });

	// When the user clicks on <span> (x), close the modal
	$("#close-modal").click(function(event) 
	{
    document.getElementById('my-modal').style.display = "none";
    // clear the modal
    document.getElementById('open-resource-editor').innerHTML = "";
		document.getElementById('resource-container').innerHTML = "";

    tinymce.remove();
  });
  
  $('#resource-name').keypress(function(e){ return e.which != 13; });
});

// When the user clicks anywhere outside of the modal, close it
window.onmousedown = function(event) 
{
    if (event.target == document.getElementById('my-modal')) 
    {
      document.getElementById('my-modal').style.display = "none";
      // delete the edit icon
      document.getElementById('open-resource-editor').innerHTML = "";
      // clear the modal
		  document.getElementById('resource-container').innerHTML = "";
      tinymce.remove();
	}
}

// Display only the container specified.
function displayContainer(container) {
  // Display this container, undisplay all other containers.
  document.getElementById(container + "-container").style.display = "block";
}
