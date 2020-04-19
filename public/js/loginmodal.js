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

	// When the user clicks on the create button, also open the modal but with the resource viewer
	$("#editor-btn").click(function(event)
	{
    var temp_resource_id = 2;
		openResourceEditor(temp_resource_id);
	});

	$("#resource-meta-btn").click(function(event)
	{
    var temp_resource_id = 2;
		openResourceViewer(temp_resource_id);
  });

  $("#resource-creator-btn").click(function(event)
  {
    openResourceEditor(2);
  });

  $('#test-resource-uses').click(function(event)
  {
    let tempServer = new Server();

    console.log("Created the temp server");

    let successCallback = (data) => {
      console.log("sucess!");
      console.log(data);
      // data.then((result) => {
      //   console.log("show me the result!");
      //   console.log(result);
      // })
    };

    let errorCallback = (error) => {
      console.log("warning");
      console.log(error);
    };

    tempServer.getResourceUseJSON(errorCallback, successCallback);
  });
  
  // When the user clicks on edit icon in resource viewer 
  $("#open-resource-editor").click(function(event) 
  {
    // get the resource id
    var temp_resource_id = document.getElementById('resource-id').innerHTML;
    // clear what is displayed in resource viewer
    document.getElementById('edit-icon').style.display = "none";
    document.getElementById('modules').innerHTML = " "; //clean the display box up
    document.getElementById('resource-head').innerHTML = " ";

    // change the url from /resources/{resource_id} to 
    // /resources/{resource_id}/edit
    history.pushState({},'','edit');

    openResourceEditor(temp_resource_id);
  });

	// When the user clicks on <span> (x), close the modal
	$("#close-modal").click(function(event) 
	{
    document.getElementById('my-modal').style.display = "none";
    document.getElementById('edit-icon').style.display = "none";
		document.getElementById('resource-head').innerHTML = " ";
		document.getElementById('modules').innerHTML = " "; //clean the display box up
    resetContentNum();
    tinymce.remove();
	});
});

// When the user clicks anywhere outside of the modal, close it
window.onmousedown = function(event) 
{
    if (event.target == document.getElementById('my-modal')) 
    {
      document.getElementById('my-modal').style.display = "none";
      document.getElementById('edit-icon').style.display = "none";
      document.getElementById('resource-head').innerHTML = " ";
      document.getElementById('modules').innerHTML = " "; //clean the display box up
      resetContentNum();
      tinymce.remove();
	}
}

// Display only the container specified.
function displayContainer(container) {
  // Display this container, undisplay all other containers.
  document.getElementById(container + "-container").style.display = "block";
}

// When the user clicks on the create button, also open the modal but with the resource viewer
function openResourceCreator(nodeId) {
  document.getElementById("my-modal").style.display = "block";
  displayContainer("resource");
  createNewResource(nodeId);
  $('select[name="attach').selectstyle({
    width: 400,
    height: 300,
    theme: "light",
    onchange: function(val) {}
  });
}
