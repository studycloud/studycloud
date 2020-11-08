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

	// TODO: delete the button and this, testing only
	$("#editor-btn").click(function(event)
	{
    var temp_resource_id = 2;
    var resourceModal= new ResourceModal(type = "edit", resource_id = temp_resource_id);
		resourceModal.openResourceEditor();
	});

  // TODO: delete the button and this, testing only
	$("#resource-meta-btn").click(function(event)
	{
    var temp_resource_id = 2;
    var resourceModal = new ResourceModal(type = "view", resource_id = temp_resource_id);
		resourceModal.openResourceViewer();
  });

  // TODO: delete the button and this, testing only
  $("#resource-creator-btn").click(function(event)
  {
    var temp_node_id = 10;
    var resourceModal = new ResourceModal(type = "create", resource_id = temp_node_id);
		resourceModal.openResourceCreator();
  });
  
  // // When the user clicks on edit icon in resource viewer 
  // $("#open-resource-editor").click(function(event) 
  // {
  //   // get the resource id
  //   var temp_resource_id = document.getElementById('resource-id').innerHTML;
    
  //   // clear what is displayed in resource viewer
  //   document.getElementById('edit-icon').style.display = "none";
  //   // clear the modal
  //   document.getElementById('resource-container').innerHTML = "";

  //   // change the url from /resources/{resource_id} to 
  //   // /resources/{resource_id}/edit
  //   history.pushState({},'',window.location.href+'/edit');

  //   var resourceModal= new ResourceModal(type = "edit", resource_id = temp_resource_id);
	// 	resourceModal.openResourceEditor(temp_resource_id);
  // });

	// When the user clicks on <span> (x), close the modal
	$("#close-modal").click(function(event) 
	{
    document.getElementById('my-modal').style.display = "none";
    document.getElementById('edit-icon').style.display = "none";
    // clear the modal
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
      document.getElementById('edit-icon').style.display = "none";
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
