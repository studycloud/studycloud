// use when we have more than 1 content
var content_num = 0;
// use this to fix problem with "add new content" but not submitting
// having this will clear any unsaved changes
var pre_updated_content_num = content_num;

/**
 * TODO: 
 * 2. Ask: does the resource creator takes the node id?
 * 3. Problem that might not be a problem in the future? 
 * 	http://127.0.0.1:8000/resources/2/edit/edit
 *  in login modal, now it will just append edit to the current url
 * 	Hopefully in the future, it will redirect to the right url after we submit the content?
 */

/////////////////////////////////////////////////////////////////////////////////////////////////
//
//	Wrapper functions for integrating with the tree
// 
/////////////////////////////////////////////////////////////////////////////////////////////////

/**
 * Wrapper function to open up the resource editor
 * @param {*} resource_id int, resource id for the resource
 */
function openResourceEditor(resource_id) {
	document.getElementById('my-modal').style.display = "block";
	document.getElementById('edit-icon').style.display = "none";
	displayContainer("resource");

	var server = new Server();
	server.getResourceUseJSON((error) => {
			console.log("Get resource use error");
			console.log(error);
		}, resourceEditorHTML);
	server.getResource(resource_id, 
		(error) => {
			console.log("Open resource editor error");
			console.log(error);
		},
		// creating an anonymous function so we can pass in the resource_id
		// as well as receiving the data from the server
		(resource_data) => {
			fillInResourceForEditor(resource_data, resource_id);
		}
	);
}

/**
 * Wrapper function to open up the resource viewer
 * @param {*} resource_id int, resource id for the resource
 */
function openResourceViewer(resource_id) {
	document.getElementById('my-modal').style.display = "block";
	document.getElementById('edit-icon').style.display = "none";
	displayContainer("resource");

	var server = new Server();
	server.getResource(resource_id, 
		(error) => {
			console.log("Resourcer viewer error");
			console.log(error);
		}, 
		// creating an anonymous function so we can pass in the resource_id
		// as well as receiving the data from the server
		(resource_data) => {
			displayResource(resource_data, resource_id);
		}
	);
}

/**
 * Wrapper function to open the resource creator
 * @param {*} node_id_in Forgot what exactly is this... Probably the node where this resource
 * 							will branch off?
 */
function openResourceCreator(node_id_in) {
	document.getElementById('my-modal').style.display = "block";
	document.getElementById('edit-icon').style.display = "none";
	displayContainer("resource");

	var server = new Server();
	// use the same template as resource editor
	server.getResourceUseJSON(
		(error) => {
			console.log("Get resource use error");
			console.log(error);
		}, 
		// creating an anonymous function so we can pass in the node_id
		// as well as receiving the data from the server
		(resourceUseData) => {
			resourceCreatorHTML(resourceUseData, node_id_in);
		}
	);
}

/////////////////////////////////////////////////////////////////////////////////////////////////
//
//	Resource Viewer functions
//
/////////////////////////////////////////////////////////////////////////////////////////////////

/** 
 * \brief display resources on resource viewer
 * @param {*} received a response (needs to turn into a json)
 */
function displayResource(received, resource_id) {
  /*
		received.json() gives us a Promise
		.then(function(resource){
			...
		} is an anonymous function
			resource is the json we want
	*/
	
	document.getElementById('edit-icon').style.display = "block";

	received.json().then(function(resource){
		console.log(resource);
		document.getElementById('resource-head').innerHTML = "\
			<div id = 'resource-id' style='visibility: hidden'> </div>\
			<div><h1 id = 'resource-name'>"+resource.meta.name+"</h1>\
			<div>contributed by <div id='author-name'></div></div>";
		
		document.getElementById("resource-id").innerHTML = resource_id;

		display_author(resource.meta.author_name, resource.meta.author_type);
		for (var i = 0; i < resource.contents.length; i++) {
		display_content(i, resource.contents[i]);
		}
  });
}

/**
 * \brief display author's name and type
 * @param {*} name String, author's name
 * @param {*} type String, author's type
 */
function display_author(name, type) {
	// Clear all classes on the author-name field.
	var cl = document.getElementById("author-name").classList;
	for (var i = cl.length; i > 0; i--) {
	cl.remove(cl[0]);
	}
	document.getElementById("author-name").classList.add(type);
	document.getElementById("author-name").innerHTML = name;
}

/**
 * @param {*} num content's index in the content array
 * @param {*} element json for that content
 *
 */
function display_content(num, element)
{
	// Create a new module.
	document.getElementById('modules').innerHTML+="<div class=module id='module-"+num+"'></div>";

	// TODO: very inefficient way to decide how contents are displayed
	if(element.type=="link")
	{
		// tinyMCE tends to wrap content in <p> </p> which will affect the link
		var display_link = element.content.replace( /(<([^>]+)>)/ig, '');
		if (!display_link.includes("https://")){
			display_link = "https://" + display_link;
		}
		document.getElementById('module-'+num).innerHTML+="<div><a href="+display_link+" target='_blank'>"+element.name+"</a></div>";
	}
	else // Apparently by MVP things are HTML text. Check this. 
	{
		document.getElementById('module-'+num).innerHTML+="<div class=resource-divider></div><h2>"+element.name+"</h2><div>"+element.content+"</div>";
	}
	// Add other types as you will. 

	// Display dates. 
	document.getElementById('module-'+num).innerHTML+="<div class='date'>Created: "+element.created+"</div>";
	document.getElementById('module-'+num).innerHTML+="<div class='date'>Modified: "+element.modified+"</div>";
}

/////////////////////////////////////////////////////////////////////////////////////////////////
//
//	Resource Editor functions
//
/////////////////////////////////////////////////////////////////////////////////////////////////

/** 
 * \brief fill in html for resource editor
 * \details gets called in loginmodal.js when the editor button gets clicked
 * 			TODO: need to be implemented in the tree in the future
 */
function resourceEditorHTML(resourceUseData)
{
	// create all the input to create resources
	document.getElementById('resource-head').innerHTML="\
		<div id = 'resource-id' style='visibility: hidden'> </div>\
		<div id = 'resource-name' contenteditable=true> Resource Name </div>";
	document.getElementById('modules').innerHTML = "\
		<div class=resource-modal-label> Resource Use:</div>\
		<br>" + selectorCodeGenerator("resource-use", resourceUseData) + "<br>\
		<div class=resource-divider></div>\
		<div class = 'content-creator'>\
		<div class=resource-modal-label>Resource Content Name:</div><br>\
		<div class=content-name id ='content-name0' contenteditable=true> Content Name </div> <br>\
		<div class=resource-modal-label> Content Type: </div>\
		<br>" + selectorCodeGenerator("content-type") + "<br>\
		<div class=resource-modal-label>Content:</div>\
		<br> <textarea rows = '5' id = 'tinymce'> </textarea> </div> <div id = 'more-contents'> </div>\
		<input type = 'checkbox' id = 'profPermission'>\
		<label for = 'profPermission' id = 'labelProfPermission'> It is okay with my professor to edit this resource. </label>\
		<span style = 'color:red' display = 'inline'>* </span>\
		<div> <button type = 'button' id = 'submit-button' onclick = 'submitEditedResource()'> Submit </button>\
		<button type = 'button' id = 'cancel-button' onclick = 'newContent()'> Cancel </button>";
}

/** 
 * @param {*} received a response (needs to turn into a json)
 * \details load the corresponding resource in textfield
 * 				(resource specified by resource_id)
 * 		the user has to be the author to edit
 */
function fillInResourceForEditor(received, resource_id)
{
	/*
		received.json() gives us a Promise
		.then(function(resource){
			...
		} is an anonymous function
			resource is the json we want
	*/
	received.json().then(function(resource){
		console.log(resource);

		document.getElementById("resource-id").innerHTML = resource_id;
		document.getElementById("resource-name").innerHTML = resource.meta.name;
			
		loadSelectedUseOrType("resource-use-selector", resource.meta.use_name);

		// create a text area for each content
		for (i=1; i < resource.contents.length; ++i)
		{
			newContent();
		}

		loadContent(resource.contents);
	});
}

/** 
 * \brief Submit an editted resource
 * \details gets triggered with the submit function is clicked in Resource Editor
 *			Create a resource JSON (w/ resource id & content id)
 *			Call the server to edit the resource
*/
function submitEditedResource() 
{
	var profPermission = document.getElementById("profPermission");

	// user verified they got permission from their prof
	if (profPermission.checked) {
		tinymce.get("tinymce").save();
		var resource_name = document.getElementById("resource-name").innerHTML;
		var resource_id = document.getElementById("resource-id").innerHTML;
		var resource_use = findUseOrType("resource-use-selector");
		// TODO: Right now, assume that each resource only has 1 content
		// 	so content_id is the same as resource_id
		var content_id = resource_id;

		var content = document.getElementById("tinymce").value;
		// NOTE: not so good hack to solve the problem:
		// if the tinymce has code block, it will like to randomly add 
		// <code> tag when the user hits enter
		content = content.replace(new RegExp("<code></code>", "g"), "");

		// content_num = pre_updated_content_num;
		
		// store all the data in json
		// NEED TO INCLUDE: resouce id, content id
		// TODO: can't create additional content (this content doesn't have id)
		var resource =  
		{
			"id": resource_id,
			"name":resource_name,
			"use_id": resource_use,
			"contents":
			[
				{
					"id": content_id,
					"name": document.getElementById("content-name0").innerHTML,
					"type": findUseOrType("content-type-selector").toLowerCase(),
					"content": content
				}
			]
		};
		
		// TODO: For more contents in future, type is not correct
		// for (i = 1; i < (content_num+1); i++)
		// {
		// 	var content_array =
		// 	{
		// 		"name": document.getElementById("content-name"+i).innerHTML,
		// 		"type": tempType,
		// 		"content": document.getElementById("content"+i).value
		// 	};
		// 	resource.contents.push(content_array);
		// }

		console.log(resource);

		// call the server to edit the resource
		var server = new Server();

		server.editResource(resource_id, resource, 
			(error) => {
				console.log("Edit resource - error");
				console.log(error);
			}, 
			(data) => {
				console.log("Edit resource - success");
				console.log(data);
			}
		);

		// close the content editor
		document.getElementById('my-modal').style.display = "none";
		document.getElementById('resource-head').innerHTML = " ";
		document.getElementById('modules').innerHTML = " "; //clean the display box up
	
		// remove instance of tinymce
		tinymce.remove();
	} else {
		labelProfPermission = document.getElementById('labelProfPermission');
		labelProfPermission.style.color = "red";
	}

	
}

/////////////////////////////////////////////////////////////////////////////////////////////////
//
//	Resource Creator functions
//
/////////////////////////////////////////////////////////////////////////////////////////////////

/** 
 * \brief fill in html for resource creator
 * \details gets called in loginmodal.js when the creator button gets clicked
 * 			TODO: need to be implemented in the tree in the future
 * @param {*} nodeId the nodeID of where the resource will be attached (actually not sure...)
 */
function resourceCreatorHTML(resourceUseData, nodeId)
{
	// create all the input to create resources
	document.getElementById('resource-head').innerHTML="\
		<div> <div class = 'tooltip'> Edit </div>\
		<div id = 'resource-name' onmouseout = hideTooltip(this) onmouseover = showTooltip(this) onClick = hideTooltip(this)\
		contenteditable=true> Resource Name </div></div>";
	document.getElementById('modules').innerHTML = "\
		<div class=resource-modal-label> Resource Use:</div>\
		<br>" + selectorCodeGenerator("resource-use", resourceUseData) + "<br>\
		<div class=resource-divider></div>\
		<div class = 'content-creator'>\
		<div class=resource-modal-label>Resource Content Name:</div><br>\
		<div> <div class = 'tooltip'> Edit </div>\
		<div class=content-name id ='content-name0' contenteditable=true\
		onmouseout = hideTooltip(this) onmouseover = showTooltip(this) onClick = hideTooltip(this)> Content Name </div> <br>\
		<div class=resource-modal-label> Content Type: </div>\
		<br>" + selectorCodeGenerator("content-type") + "<br>\
		<div class=resource-modal-label>Content:</div>\
		<br> <textarea rows = '5' id = 'tinymce'> </textarea> </div> <div id = 'more-contents'> </div>\
		<input type = 'checkbox' id = 'profPermission'>\
		<label for = 'profPermission' id = 'labelProfPermission'> I have gotten permission from my professor to post this resource. </label>\
		<span style = 'color:red'>* </span>\
		<div> <button type = 'button' id = 'submit-button' onclick = 'submitNewResource("+nodeId+")'> Submit </button>\
		<button type = 'button' id = 'cancel-button' onclick = 'newContent()'> Cancel </button>";

	addTinyMCE();
}

/** 
 * \brief Submit a new resource
 * \details Gets triggered when the submit function is clicked in Resource Creator
 *			Create a resource JSON (w/ resource id & content id)
 *			Call the server to edit the resource
 */
function submitNewResource(node_id_num) {
	var profPermission = document.getElementById("profPermission");

	// checks that the user checked the checkbox
	if (profPermission.checked) {
		tinymce.get("tinymce").save();
		var resource_name = document.getElementById("resource-name").innerHTML;
		var resource_use = findUseOrType("resource-use-selector");
		var class_id = node_id_num.toString();
		var content = document.getElementById("tinymce").value;

		// NOTE: not so good hack to solve the problem:
		// if the tinymce has code block, it will like to randomly add 
		// <code> tag when the user hits enter
		content = content.replace(new RegExp("<code></code>", "g"), "");

		//store all the data in json
		//PROBLEM: can only create 1 content for 1 resource
		var resource = {
			name: resource_name,
			use_id: resource_use,
			class_id: class_id,
			contents: [
				{
					name: document.getElementById("content-name0").innerHTML,
					type: findUseOrType("content-type-selector").toLowerCase(),
					content: content,
				}
			]
		};

		// TODO: For when a resource have multiple contents, not MVP
		// for (i = 1; i < content_num + 1; i++) {
		// 	var content_array = {
		// 		name: document.getElementById("content-name" + i).value,
		// 		type: document.getElementById("content-type" + i).value,
		// 		content: document.getElementById("content" + i).value
		// 	};
		// 	resource.contents.push(content_array);
		// }

		console.log(resource);

		//call the server to add resource
		var server = new Server();
		server.addResource(resource, 
			(error) => {
				console.log("Create resource - error");
				console.log(error);
			}, 
			(data) => {
				console.log("Create resource - success");
				console.log(data);
		});

		//close the content creator
		document.getElementById("my-modal").style.display = "none";
		document.getElementById("resource-head").innerHTML = " ";
		document.getElementById("modules").innerHTML = " "; //clean the display box up
		
		// remove instance of tinymce
		tinymce.remove();
	} else {
		console.log("User has not checked the prof permission checkbox");
		labelProfPermission = document.getElementById('labelProfPermission');
		labelProfPermission.style.color = "red";
	}
}
  
/////////////////////////////////////////////////////////////////////////////////////////////////
//
// Helper functions
//
/////////////////////////////////////////////////////////////////////////////////////////////////

/** 
 * \brief Initialize TinyMCE
 */
function addTinyMCE() {
	tinymce.init({
		selector: "#tinymce",
		// disable menubar (file, edit, etc.)
		menubar: false,
		// allow tinyMCE to have something close to tab indent
		setup: function(ed) {
			ed.on('keydown', function(evt) {
				if (evt.keyCode == 9){ // tab pressed	
					ed.execCommand('mceInsertContent', false, '&emsp;&emsp;'); // inserts tab
					evt.preventDefault();
				}
			});
		},
		style_formats: [
			{title: 'Headers', items: [
				{title: 'Header 1', format: 'h1'},
				{title: 'Header 2', format: 'h2'},
				{title: 'Header 3', format: 'h3'},
				{title: 'Header 4', format: 'h4'},
				{title: 'Header 5', format: 'h5'},
				{title: 'Header 6', format: 'h6'}
			]},
			{title: 'Inline', items: [
				{title: 'Bold', icon: 'bold', format: 'bold'},
				{title: 'Italic', icon: 'italic', format: 'italic'},
				{title: 'Underline', icon: 'underline', format: 'underline'},
				{title: 'Strikethrough', icon: 'strikethrough', format: 'strikethrough'},
				{title: 'Superscript', icon: 'superscript', format: 'superscript'},
				{title: 'Subscript', icon: 'subscript', format: 'subscript'},
				{title: 'Code', icon: 'code', format: 'code'}
			]},
			{title: 'Blocks', items: [
				{title: 'Paragraph', format: 'p'},
				{title: 'Blockquote', format: 'blockquote'}
			]},
			{title: 'Alignment', items: [
				{title: 'Left', icon: 'alignleft', format: 'alignleft'},
				{title: 'Center', icon: 'aligncenter', format: 'aligncenter'},
				{title: 'Right', icon: 'alignright', format: 'alignright'},
				{title: 'Justify', icon: 'alignjustify', format: 'alignjustify'}
			]}
		],
		content_style: 'blockquote {\
			background: #f9f9f9;\
			border-left: 10px solid #ccc;\
			margin: 1.5em 10px;\
			padding: 0.5em 10px;\
			quotes: "\201C""\201D""\2018""\2019";\
		}' +
		'code {\
			background: #f4f4f4;\
			border: 1px solid #ddd;\
			border-left: 3px solid #f36d33;\
			color: #666;\
			page-break-inside: avoid;\
			font-family: monospace;\
			font-size: 15px;\
			line-height: 1.6;\
			margin-bottom: 1.6em;\
			max-width: 100%;\
			overflow: auto;\
			padding: 1em 1.5em;\
			display: block;\
			word-wrap: break-word;\
		}',
	});
}

/** 
 * \details receive an array of jsons
 * 		used in:
 * 			resource editor (initially loading resource)
 *			new content button (loading the previously typed contents back to the textbox)
 */
function loadContent(contents)
{
	for (i=0; i < contents.length; i++)
	{
		document.getElementById("content-name"+i).innerHTML = contents[i]["name"];
		document.getElementById("content-name"+i).value = contents[i]["name"];
		loadSelectedUseOrType("content-type-selector", contents[i]["type"]);

		// TODO: this will be problematic once we have multiple contents
		document.getElementById("tinymce").value = contents[i]["content"];
		addTinyMCE();
		tinymce.get("tinymce").load();
	}
}

/**
 * Helper function for Creating Resource Use/Content Type Selector
 * \details uses two arrays: resourceUseData and contentTypeData
 * 			which are loaded in resource.blade.php
 * 			when a specific resource is displayed (specified by url: /resources/{resource_id}/edit)
 * \returns html code for use/type selector
 * @param {*} selectorFor String, determines if it's selector for resource use or content type
 * 							Either: "resource-use" or content-type"
 */
function selectorCodeGenerator(selectorFor, data) 
{
	var name = "default";
	var ulId = "default-selector";  
	var inputId = "";

	// html code for selector
	var html_code = "<div class='use-list-scrolling-wrapper'>";

	if (selectorFor == "resource-use") {
		// set up relevant variables for resource-use selector
		name = "resource-use";
		inputClass = "use";
		ulId = "resource-use-selector";
		inputId = "";

		html_code += "<ul id='" + ulId +"'>";

		/** resourceUseData (an array)
		 * 		where all the content types are stored in resourceUseData (an array)
		 * 		loaded in resource.blade.php
		 * 		format:
		 * 			 [	{"id":1,"name":"Class Notes"},
		 * 				{"id":3,"name":"Flashcards"},
		 * 				{"id":2,"name":"Notes"}	]
		 */
		for (var i = 0; i < data.length; ++i) {
			html_code += "\
			<li><input type='radio' name='" + name + "' id='"+ inputId +""+ data[i].id +"'>\
				<label for='" + inputId +""+ data[i].id + "'>" + data[i].name + "</label></li>";
		}
		html_code +=  "</ul></div>";
	}
	else if (selectorFor == "content-type") {
		name = "content-type";
		inputClass = "type";
		ulId = "content-type-selector";
		var inputId = "t";
		dictionary = contentTypeData;
		html_code += "<ul id='" + ulId +"'>";

		/** contentTypeData (an array)
		 * 		where all the content types are stored in contentTypeData (an array)
		 * 		loaded in resource.blade.php
		 * 		format:
		 * 			["type1", "type2", "type3"]
		 */
		for (var i = 0; i < dictionary.length; ++i) {
			html_code += "\
			<li><input type='radio' name='" + name + "' id='"+ inputId +""+ i +"'>\
				<label for='" + inputId +""+ i + "'>" + contentTypeData[i] + "</label></li>";
		}
		
		html_code +=  "</ul></div>";

		// add invisible error message to be displayed if user does not select a use
	}
	

  return html_code;
}

/** Helper function for changing textbox tooltip visibility
 * \brief	Used in create resource modal, edit resource modal
 * 			Used to set a tooltip's visibility to hidden
 * \warning will only hide ttSib's first sibling of class tooltip
 * 
 * @param {*} ttSib Parent of the tooltip to be hidden 
 */
function hideTooltip(ttSib) { 
	// 
	var tooltip = ttSib.parentNode.getElementsByClassName("tooltip")[0];
	tooltip.style.visibility = 'hidden';

	// it looks like the on hover property is getting overwritten by this :( see if there's a way to make it override this?
}

/** Helper function for changing textbox tooltip visibility
 * \brief	Used in create resource modal, edit resource modal
 * 			Used to set a tooltip's visibility to visible
 * \warning will only hide ttSib's first sibling of class tooltip
 * 
 * @param {*} ttSib Parent of the tooltip to be hidden 
 */
function showTooltip(ttSib) {
	var tooltip = ttSib.parentNode.getElementsByClassName("tooltip")[0];
	tooltip.style.visibility = 'visible';
}

/**
 * Helper function for Resource Use/Content Type Selector
 * \brief 	For Resource Editor.
 * 			Load/select the resource use or content type of the resource in Resource Editor
 * 
 * @param {*} ulId String, determines if we are loading for resource use or content type
 * 			either: "resource-use-selector" or "content-type-selector"
 * @param {*} selected String, the selected use/type of this resource (from the json we get)
 */
function loadSelectedUseOrType (ulId, selected)
{
	var ul = document.getElementById(ulId);
	var listInsideUl = ul.getElementsByTagName("li");
	for (var ele of listInsideUl) {
		// get the name of the label
		var name = ele.getElementsByTagName("label")[0].innerHTML;
		if (name.toLowerCase() == selected.toLowerCase()) {
			// checked is true when this input is checked
			// select this input
			ele.getElementsByTagName("input")[0].checked = true;
		}
	}
}

/**
 * Helper function for Submitting an edited resource
 * \brief 	Find the resource use id (int) or 
 * 				 the content type name (string)
 * 			If the user has not selected one or more of the above, displays
 * 				an appropriate error message
 * 
 * @param {*} ulId String, determines if we finding resource use or content type
 * 			either: "resource-use-selector" or "content-type-selector"
 */
function findUseOrType(ulId) 
{
	var ul = document.getElementById(ulId);
	var listInsideUl = ul.getElementsByTagName("li");

	// switches to True if one of the list items has been selected
	var displayError = False;

	for (var ele of listInsideUl) {
		if (ele.getElementsByTagName("input")[0].checked == true) {
			displayError = True;
			if (ulId == "resource-use-selector") {
				return parseInt(ele.getElementsByTagName("input")[0].id);
			} 
			else if (ulId == "content-type-selector") {
				return ele.getElementsByTagName("label")[0].innerHTML;
			}
		}
	}

	// displays an error depending on ulID
	if (displayError) {
		if (ulId == "resource-use-selector") {

		} else if (ulId == "content-type-selector") {

		}
	} 

}

/////////////////////////////////////////////////////////////////////////////////////////////////
//
// Helper functions for resources with more than one content
// Warning: Currently not in use
//
/////////////////////////////////////////////////////////////////////////////////////////////////

/** 
 * \brief Create a new entry area for a new content
 * \warning Currently not in use
 */
function newContent()
{
	// an arrary of jsons, storing the entries
	var storedContent = temporaryStoreContent(); 
	
	// use pre_updated_content_num so user can decide to add new content but not submit it
	// if user exit resource editor/creator, clear previous entries
	pre_updated_content_num += 1;
	document.getElementById('more-contents').innerHTML += "<div id='content-"+pre_updated_content_num+"'></div>";
	document.getElementById('content-'+pre_updated_content_num).innerHTML += "<div class=resource-divider></div> <br> </div> <div class = 'content-creator'> Resource Content Name: <br> \
	<input type = 'text' id = 'content-name"+pre_updated_content_num+"'> <br> \
	Content Type:  <select id = 'content-type"+pre_updated_content_num+"'> <option value = 'text'> Text </option> <option value = 'link'> Link </option> </select> <br> \
	Content: <br> <textarea rows = '5' id = 'content"+pre_updated_content_num+"'> </textarea> </div> </form>";

	// load the stored content back to the content textboxes
	loadContent(storedContent);
}

/**
 * \details clear unsaved changes
 * 		when the user close the resource editor/creator,
 * 		reset pre_updated_content_num
 */
function resetContentNum() {
	pre_updated_content_num = content_num;
}

/**
 * \details helper function return an array of jsons
 * 		store previously typed contents
 * 		used in:
 * 			newContent() (store before create new textarea for new content)
 * \warning not in use right now
 */
function temporaryStoreContent() {
	var contents = [
	  {
		name: document.getElementById("content-name0").value,
		type: document.getElementById("content-type0").value,
		content: document.getElementById("content0").value
	  }
	];
  
	// use pre_updated_content_num here because...
	// If the user has entries in a new content (but hasn't click submit)
	// then she clicks on "New Content" again,
	// using pre_updated_content_num will save his entries
	for (i = 1; i < pre_updated_content_num + 1; i++) {
	  var contentArray = {
		name: document.getElementById("content-name" + i).value,
		type: document.getElementById("content-type" + i).value,
		content: document.getElementById("content" + i).value
	  };
	  contents.push(contentArray);
	}
  
	return contents;
  }
