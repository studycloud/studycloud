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

	// When the user clicks on <span> (x), close the modal
	$("#close-modal").click(function(event) 
	{
	    document.getElementById('my-modal').style.display = "none";
	});

});

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) 
{
    if (event.target == document.getElementById('my-modal')) 
    {
        document.getElementById('my-modal').style.display = "none";
    }
}

// Display only the container specified.
function displayContainer(container) 
{
	// Display this container, undisplay all other containers. 
	var containers = ["forget", "register", "resource"];
	for(var i = 0; i < 3; i++) 
	{
		if (containers[i] == container)
		{
			document.getElementById(containers[i]+'-container').style.display = "block";
		}
		else
		{
			document.getElementById(containers[i]+'-container').style.display = "none";
		}
	}

}

