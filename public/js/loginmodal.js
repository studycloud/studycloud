$(document).ready(function()
{ 

	// When the user clicks on the register button, open the modal but with the register content
	$("#register-btn").click(function(event) 
	{
	    document.getElementById('my-modal').style.display = "block";
		displayContainer("register");   
	});

	// When the user clicks on the forget button, also open the modal but with the forget content
	$("#forget-btn").click(function(event)
	{
	    document.getElementById('my-modal').style.display = "block";
		displayContainer("forget");
	});

	// When the user clicks on the create button, also open the modal but with the resource viewer
	$("#editor-btn").click(function(event)
	{
		document.getElementById('my-modal').style.display = "block";
		displayContainer("resource");
		resourceEditorHTML();
		editResource();
	});

	$("#resource-meta-btn").click(function(event)
	{
		document.getElementById('my-modal').style.display = "block";
		displayContainer("resource");
		viewResource();
	});

	// When the user clicks on <span> (x), close the modal
	$("#close-modal").click(function(event) 
	{
		document.getElementById('my-modal').style.display = "none";
		document.getElementById('resource-head').innerHTML = " ";
		document.getElementById('modules').innerHTML = " "; //clean the display box up
		resetContentNum();
		//location.reload();
	});

});

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) 
{
    if (event.target == document.getElementById('my-modal')) 
    {
		document.getElementById('my-modal').style.display = "none";
		document.getElementById('resource-head').innerHTML = " ";
		document.getElementById('modules').innerHTML = " "; //clean the display box up
		resetContentNum();
		/*
		var x = document.getElementsByClassName("module");
		for (var i; i<x.length; i++){
			x[i].innerHTML=" ";
		}*/
		
		//location.reload();
    }
}

// Display only the container specified.
function displayContainer(container) 
{
	// Display this container, undisplay all other containers. 
	document.getElementById(container+'-container').style.display = "block";

}
