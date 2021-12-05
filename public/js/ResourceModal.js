/**
 * Resource Modals class
 * @param {*} type string, 3 options
 * 		"view": open the resource viewer
 * 		"edit": open the resource editor
 * 		"create": open the resource creator
 */
class ResourceModal {
	constructor() {
		// self is a special variable that contains a reference to the class instance itself. 
		//	This is created in every function so that we can use anonymous functions
		var self = this;

		// Server object so we can get, update and create resources
		self.server = new Server();

		// use when we have more than 1 content
		self.content_num = 0;
		// use this to fix problem with "add new content" but not submitting
		// having this will clear any unsaved changes
		self.pre_updated_content_num = self.content_num;

		self.DEFAULT_RESOURCE_USE = "Class Notes";
		self.DEFAULT_CONTENT_TYPE = "text";
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////
	//
	//	Wrapper functions for integrating with the tree
	// 
	/////////////////////////////////////////////////////////////////////////////////////////////////
	/**
	 * Wrapper function to open up the resource viewer
	 */
	openResourceViewer(resource_id) {
		var self = this;

		self.resource_id = resource_id;
		self.type = "view";

		document.getElementById('my-modal').style.display = "block";
		document.getElementById("resource-container").style.display = "block";

		self.server.getResource(self.resource_id,
			// failure callback
			(error) => {
				console.log("Resourcer viewer error");
				console.log(error);
			},
			// success callback
			(resource_data) => {
				self.displayResource(resource_data);
			}
		);
	}

	/**
	 * Wrapper function to open up the resource editor
	 */
	openResourceEditor(resource_id) {
		var self = this;

		self.resource_id = resource_id;
		self.type = "edit";

		document.getElementById('my-modal').style.display = "block";
		document.getElementById("resource-container").style.display = "block";

		self.server.getResourceUseJSON((error) => {
			console.log("Get resource use error");
			console.log(error);
		}, (resourceUseData) => {
			self.resourceEditorCreatorHTML(resourceUseData);
		});
		self.server.getResource(self.resource_id,
			(error) => {
				console.log("Open resource editor error");
				console.log(error);
			}, (resourceData) => {
				self.fillInResourceForEditor(resourceData);
			});
	}

	/**
	 * Wrapper function to open the resource creator
	 */
	openResourceCreator(resource_id) {
		var self = this;

		self.resource_id = resource_id;
		self.type = "create";

		document.getElementById('my-modal').style.display = "block";
		document.getElementById("resource-container").style.display = "block";

		// use the same template as resource editor
		self.server.getResourceUseJSON(
			(error) => {
				console.log("Get resource use error");
				console.log(error);
			}, 
			// creating an anonymous function so we can pass in the node_id
			// as well as receiving the data from the server
			(resourceUseData) => {
				self.resourceEditorCreatorHTML(resourceUseData);
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
	displayResource(received) {
		/*
			  received.json() gives us a Promise
			  .then(function(resource){
				  ...
			  } is an anonymous function
				  resource is the json we want
		*/
		var self = this;
		
		// add the edit icon
		document.getElementById("open-resource-editor").innerHTML = "<i id='edit-icon' display='none' class='fas fa-edit'></i>";

		document.getElementById('edit-icon').addEventListener('click', () => {
			// clear the modal	
			document.getElementById('resource-container').innerHTML = "";
			// delete the edit icon
			document.getElementById('open-resource-editor').innerHTML = "";

			// change the url from /resources/{resource_id} to 
			// /resources/{resource_id}/edit
			history.pushState({},'',window.location.href+'/edit');
		
			self.openResourceEditor(self.resource_id);
		})

		received.json().then(function (resource) {
			// set the template of the page
			document.getElementById('resource-container').innerHTML =
				"<div class='resource-background'>" +
				"<div id='resource-head-display'></div>" +
				"<div id='modules-display'>" +
				"<!-- This is where you put the modules. -->" +
				"</div>" +
				"<div id='buttons'></div>" +
				"</div>";
			// set the resource head (name and author)
			document.getElementById('resource-head-display').innerHTML =
				"<div id = 'resource-id' style='visibility: hidden'></div>" +
				"<div id = 'resource-name-display'>" + resource.meta.name + "</div>" +
				"<div id = 'author-info'>contributed by <div id='author-name'></div></div>";

			self.display_author(resource.meta.author_name, resource.meta.author_type);

			for (var i = 0; i < resource.contents.length; i++) {
				self.display_content(i, resource.contents[i]);
			}
		});
	}

	/**
	 * \brief display author's name and type
	 * @param {*} name String, author's name
	 * @param {*} type String, author's type
	 */
	display_author(name, type) {
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
	display_content(num, element) {
		// Create a new module.
		document.getElementById('modules-display').innerHTML += "<div class=resource-divider></div><div class=module id='module-" + num + "'></div>";

		// TODO: very inefficient way to decide how contents are displayed
		if (element.type == "link") {
			// tinyMCE tends to wrap content in <p> </p> which will affect the link
			// 	so we need to remove them
			var display_link = element.content.replace(/(<([^>]+)>)/ig, '');

			if (!display_link.includes("https://")) {
				display_link = "https://" + display_link;
			}

			document.getElementById('module-' + num).innerHTML +=
				"<div class='content-title'>" +
				"<a href=" + display_link + " target='_blank'>" + element.name + "</a>" +
				"</div>";
		}
		else // Apparently by MVP things are HTML text. Check this. 
		{
			document.getElementById('module-' + num).innerHTML +=
				"<div class='content-title'>" +
				"<h2>" + element.name + "</h2>" +
				"</div>" +
				"<div id='content-" + num + "' class='content'>" +
				element.content +
				"</div>";
		}
		// Add other types as you will. 
		// Display dates. 
		document.getElementById('module-' + num).innerHTML += "<div id='created-date' class='date'>Created: " + element.created + "</div>";
		document.getElementById('module-' + num).innerHTML += "<div id='modified-date' class='date'>Modified: " + element.modified + "</div>";
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
	resourceEditorCreatorHTML(resourceUseData)
	{	
		var self = this;
		
		// set the template of the page
		document.getElementById('resource-container').innerHTML = 
			"<div class='resource-background'>" + 
				"<div id='resource-head'></div>" + 
				"<div id='modules'>" +
					"<!-- This is where you put the modules. -->" +
				"</div>" + 
				"<div id='buttons'></div>" + 
			"</div>";

		// create all the input to create resources
		document.getElementById('resource-head').innerHTML = 
			"<div class = 'tooltip' id = 'resource-name-tip'> Edit </div>" +
			"<span id = 'resource-name' onmouseout = hideTooltip(this) onmouseover = showTooltip(this) onClick = hideTooltip(this) " + 
				"contenteditable = true>Resource Name</span>" + 
			"<div id = 'resource-use-label' >Resource Use:</div>" + 
			self.selectorCodeGenerator("resource-use", resourceUseData);
		
		document.getElementById('modules').innerHTML = 
			"<div class=resource-divider></div>" + 
			"<div id='content-name-label'>Resource Content Name:</div>" + 
			"<div class = 'tooltip' id = 'content-name-tip'> Edit </div>" + 
			"<div class=content-name id ='content-name0' contenteditable=true " + 
			"onmouseout = hideTooltip(this) onmouseover = showTooltip(this) onClick = hideTooltip(this)>Content Name</div>" +
			"<div id='content-type-label'>Content Type:</div>" + 
			self.selectorCodeGenerator("content-type") + 
			"<div id='content-label'>Content:</div>" + 
			"<div id='content-input'>" + 
				"<textarea rows = '20' id = 'tinymce'> </textarea>" + 
			"</div>" + 
		"<div id = 'more-contents'> </div>";

		document.getElementById('buttons').innerHTML = 
			"<div id = 'warning-msg'>" + 
				"<input type = 'checkbox' id = 'profPermission'>" + 
				"<label for = 'profPermission' id = 'labelProfPermission'>" + 
					"It is okay with my professor to edit this resource." + 
				"</label>" + 
				"<span style = 'color:red' display = 'inline'>* </span>" + 
			"</div>" +
			"<div id = 'error-msg' > </div>"+
			"<button type = 'button' id = 'submit-button'> Submit </button>" +
			"<button type = 'button' id = 'cancel-button'> Cancel </button>";
		
		// if it's a resource editor
		// 		Tiny MCE will be added when we are populating the editor with data
		if (self.type == "edit") {
			document.getElementById('submit-button').addEventListener('click', () => {
				self.submitEditedResource();
			});
		} else if (self.type == "create") {
			self.addTinyMCE();	
			document.getElementById('submit-button').addEventListener('click', () => {
				self.submitNewResource();
			});
		}

		// document.getElementById('cancel-button').on('click', () => {
		// 	self.submitNewResource();
		// });
		
		
		// prevent "ENTER" from enterng the new line, then exit editting mode for resourcename
		document.getElementById('resource-name').addEventListener('keydown', (evt) => {
			if (evt.keyCode === 13) {
				evt.preventDefault();
				// exit editting mode
				document.getElementById('resource-name').blur();
			}
		});

		// users can only paste plain text into resource name
		document.getElementById('resource-name').addEventListener('paste', (evt) => {
			evt.preventDefault();
			var text = evt.clipboardData.getData("text/plain");
			document.execCommand("insertHTML", false, text);
		});

		var content_name_items = document.getElementsByClassName('content-name');

		// iterate through all the content-name (for MVP, there's only one)
		for (var i = 0; i < content_name_items.length; i++) {	
			content_name_items[i].addEventListener('keydown', (evt) => {
				if (evt.keyCode === 13) {
					evt.preventDefault();
					// exit editting mode
					evt.target.blur();
				}
			});
			
			// paste plain text into content name
			content_name_items[i].addEventListener('paste', (evt) => {
				evt.preventDefault();
				var text = evt.clipboardData.getData("text/plain");
				document.execCommand("insertHTML", false, text);
			});
		}

		// enter default values for resource use selectors, to prevent
		// the user from submitting a new resource without something selected for each 
		self.loadSelectedUseOrType("resource-use-selector", self.DEFAULT_RESOURCE_USE);
		self.loadSelectedUseOrType("content-type-selector", self.DEFAULT_CONTENT_TYPE);
	}

	/** 
	 * @param {*} received a response (needs to turn into a json)
	 * \details load the corresponding resource in textfield
	 * 				(resource specified by resource_id)
	 * 		the user has to be the author to edit
	 */
	fillInResourceForEditor(received)
	{
		/*
			received.json() gives us a Promise
			.then(function(resource){
				...
			} is an anonymous function
				resource is the json we want
		*/
		var self = this;

		received.json().then(function(resource){
			document.getElementById("resource-name").innerHTML = resource.meta.name;
				
			self.loadSelectedUseOrType("resource-use-selector", resource.meta.use_name);

			// create a text area for each content
			for (var i = 1; i < resource.contents.length; ++i)
			{
				self.newContent();
			}

			self.loadContent(resource.contents);
		});
	}
	
	/** 
	 * \brief Submit an editted resource
	 * \details gets triggered with the submit function is clicked in Resource Editor
	 *			Create a resource JSON (w/ resource id & content id)
	 *			Call the server to edit the resource
	*/
	submitEditedResource() 
	{
		var self = this;

		var profPermission = document.getElementById("profPermission");

		// user verified they got permission from their prof
		if (profPermission.checked) {
			tinymce.get("tinymce").save();

			var resource_name = document.getElementById("resource-name").innerHTML;
			
			var resource_use = self.findUseOrType("resource-use-selector");
			// TODO: Right now, assume that each resource only has 1 content
			// 	so content_id is the same as resource_id
			var content_id = self.resource_id;

			var content = document.getElementById("tinymce").value;
			// NOTE: not so good hack to solve the problem:
			// if the tinymce has code block, it will like to randomly add 
			// <code> tag when the user hits enter
			content = content.replace(new RegExp("<code></code>", "g"), "");

			// self.content_num = self.pre_updated_content_num;
			
			// store all the data in json
			// NEED TO INCLUDE: resouce id, content id
			// TODO: can't create additional content (this content doesn't have id)
			var resource =  
			{
				"id": self.resource_id,
				"name": resource_name,
				"use_id": resource_use,
				"contents":
				[
					{
						"id": content_id,
						"name": document.getElementById("content-name0").innerHTML,
						"type": self.findUseOrType("content-type-selector").toLowerCase(),
						"content": content
					}
				]
			};
			
			// TODO: For more contents in future, type is not correct
			// for (i = 1; i < (self.content_num+1); i++)
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

			self.server.editResource(self.resource_id, resource, 
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
			// highlight the prof permission
			var labelProfPermission = document.getElementById('labelProfPermission');
			labelProfPermission.style.color = "red";
		}		
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////
	//
	//	Resource Creator functions
	//
	/////////////////////////////////////////////////////////////////////////////////////////////////
	/** 
	 * \brief Submit a new resource
	 * \details Gets triggered when the submit function is clicked in Resource Creator
	 *			Create a resource JSON (w/ resource id & content id)
	*			Call the server to edit the resource
	*/
	submitNewResource()
	{
		var self = this;

		var profPermission = document.getElementById("profPermission");

		// checks that the user checked the checkbox
		if (profPermission.checked) {
			tinymce.get("tinymce").save();
			var resource_name = document.getElementById("resource-name").innerHTML;
			var resource_use = self.findUseOrType("resource-use-selector");
			var parent_class_id = self.resource_id;
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
				class_id: parent_class_id,
				contents: [
					{
						name: document.getElementById("content-name0").innerHTML,
						type: self.findUseOrType("content-type-selector").toLowerCase(),
						content: content,
					}
				]
			};

			// TODO: For when a resource have multiple contents, not MVP
			// for (i = 1; i < self.content_num + 1; i++) {
			// 	var content_array = {
			// 		name: document.getElementById("content-name" + i).value,
			// 		type: document.getElementById("content-type" + i).value,
			// 		content: document.getElementById("content" + i).value
			// 	};
			// 	resource.contents.push(content_array);
			// }

			console.log(resource);

			self.server.addResource(resource, 
				(error) => {
					console.log("Create resource - error");
					console.log(error);
					console.log(error.responseJSON);
					error.responseJSON.then((result) =>{
						console.log("getting the error message");
						console.log(result);
						document.getElementById("error-msg").innerHTML = self.cleanMessage(result);
					});
				}, 
				(data) => {
					console.log("Create resource - success");
					console.log(data);

					//close the content creator
					document.getElementById("my-modal").style.display = "none";
					document.getElementById("resource-head").innerHTML = " ";
					document.getElementById("modules").innerHTML = " "; //clean the display box up
					
					// remove instance of tinymce
					tinymce.remove();
			});
		} else {	
			var labelProfPermission = document.getElementById('labelProfPermission');
			labelProfPermission.style.color = "red";
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////
	//
	// Helper functions
	//
	/////////////////////////////////////////////////////////////////////////////////////////////////

	/** 
	 * \Takes in raw error JSON and returns cleaned string
	 */

	cleanMessage(rawmessage){
		const parsedmsg = JSON.parse(rawmessage);

		let errorMsgToDisplay = "<ul>";
		
		//iterating through each key in list of errors
		for(var key in parsedmsg["errors"]){
        	var error=parsedmsg["errors"][key];
			// iterataing through the specific err msg for each key
			console.log("error: ");
			console.log(error);
			for (var errorMessage in error){
				console.log(error[errorMessage]);
				if (error[errorMessage].includes("string")){
					if (key === 'contents.0.content'){
						errorMsgToDisplay += '<li> &bull; The content is required </li>';
					}
				}
				if (error[errorMessage].includes("is required")){
					console.log(key);
					if (key === 'name'){
						errorMsgToDisplay += '<li> &bull; The resource name is required </li>';
					}
					// TODO: make this support having multiple contents
					if (key === 'contents.0.name'){
						errorMsgToDisplay += '<li> &bull; The resource content name is required </li>';
					}
				}

			}
		}
        // work with key and value
		errorMsgToDisplay += "</ul>";
		console.log("errorMsgToDisplay");
		console.log(errorMsgToDisplay);
		return errorMsgToDisplay;
	}

	/** 
	 * \brief Initialize TinyMCE
	 */
	addTinyMCE() {
		tinymce.init(
			{
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
				content_style: 
					'blockquote { background: #f9f9f9; border-left: 10px solid #ccc; margin: 1.5em 10px; padding: 0.5em 10px;}' +
					'code { background: #f4f4f4; border: 1px solid #ddd; border-left: 3px solid #f36d33; color: #666; page-break-inside: avoid;  font-family: monospace; font-size: 15px; line-height: 1.6; margin-bottom: 1.6em; max-width: 100%; overflow: auto; padding: 1em 1.5em;  display: block; word-wrap: break-word; }',
			}
		);
	}

	/** 
	 * \details receive an array of jsons
	 * 		used in:
	 * 			resource editor (initially loading resource)
	 *			new content button (loading the previously typed contents back to the textbox)
	*/
	loadContent(contents)
	{
		var self = this;
		for (var i=0; i < contents.length; i++)
		{
			document.getElementById("content-name"+i).innerHTML = contents[i]["name"];
			document.getElementById("content-name"+i).value = contents[i]["name"];
			self.loadSelectedUseOrType("content-type-selector", contents[i]["type"]);

			// TODO: this will be problematic once we have multiple contents
			document.getElementById("tinymce").value = contents[i]["content"];
			self.addTinyMCE();
			tinymce.get("tinymce").load();
		}
	}

	/////////////////////////////////////////////////////////////////////////////////////////////////
	//
	// Helper functions for resource user / content type selector
	//
	/////////////////////////////////////////////////////////////////////////////////////////////////

	/**
	 * Helper function for Creating Resource Use/Content Type Selector
	 * \details uses two arrays: resourceUseData and contentTypeData
	 * 			which are loaded in resource.blade.php
	 * 			when a specific resource is displayed (specified by url: /resources/{resource_id}/edit)
	 * \returns html code for use/type selector
	 * @param {*} selectorFor String, determines if it's selector for resource use or content type
	 * 							Either: "resource-use" or content-type"
	 */
	selectorCodeGenerator(selectorFor, data) 
	{
		var name = "default";
		var ulId = "default-selector";  
		var inputId = "";

		// html code for selector
		var html_code = "";

		if (selectorFor == "resource-use") {
			// set up relevant variables for resource-use selector
			name = "resource-use";
			var inputClass = "use";
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
				html_code += "<li><input type='radio' name='" + name + "' id='"+ inputId +""+ data[i].id +"'>" +
					"<label for='" + inputId +""+ data[i].id + "'>" + data[i].name + "</label></li>";
			}
		}
		else if (selectorFor == "content-type") {
			name = "content-type";
			var inputClass = "type";
			ulId = "content-type-selector";
			inputId = "t";

			var dictionary = contentTypeData;
			html_code += "<ul id='" + ulId +"'>";

			/** contentTypeData (an array)
			 * 		where all the content types are stored in contentTypeData (an array)
			 * 		loaded in resource.blade.php
			 * 		format:
			 * 			["type1", "type2", "type3"]
			 */
			for (var i = 0; i < dictionary.length; ++i) {
				html_code += "<li><input type='radio' name='" + name + "' id='"+ inputId +""+ i +"'>" +
					"<label for='" + inputId +""+ i + "'>" + contentTypeData[i] + "</label></li>";
			}

			// add invisible error message to be displayed if user does not select a use
		}
		
		html_code +=  "</ul>";
	
		return html_code;
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
	loadSelectedUseOrType(ulId, selected)
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
	findUseOrType(ulId) 
	{
		var ul = document.getElementById(ulId);
		var listInsideUl = ul.getElementsByTagName("li");

		for (var ele of listInsideUl) {
			if (ele.getElementsByTagName("input")[0].checked == true) {
				if (ulId == "resource-use-selector") {
					return parseInt(ele.getElementsByTagName("input")[0].id);
				} 
				else if (ulId == "content-type-selector") {
					return ele.getElementsByTagName("label")[0].innerHTML;
				}
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
	newContent()
	{
		var self = this;

		// an arrary of jsons, storing the entries
		var storedContent = self.temporaryStoreContent(); 
		
		// use pre_updated_content_num so user can decide to add new content but not submit it
		// if user exit resource editor/creator, clear previous entries
		self.pre_updated_content_num += 1;
		document.getElementById('more-contents').innerHTML += "<div id='content-" + self.pre_updated_content_num+"'></div>";
		document.getElementById('content-' + self.pre_updated_content_num).innerHTML += "<div class=resource-divider></div>" + 
			"<div class = 'content-creator'> Resource Content Name:" +
			"<input type = 'text' id = 'content-name" + self.pre_updated_content_num+"'>" +
			"Content Type:  <select id = 'content-type" + self.pre_updated_content_num+"'>" +
			"<option value = 'text'> Text </option> <option value = 'link'> Link </option> </select>" +
			"Content:<textarea rows = '5' id = 'content" + self.pre_updated_content_num+"'> </textarea> </div> </form>";

		// load the stored content back to the content textboxes
		self.loadContent(storedContent);
	}

	/**
	 * \details clear unsaved changes
	 * 		when the user close the resource editor/creator,
	 * 		reset pre_updated_content_num
	 */
	resetContentNum() {
		self.pre_updated_content_num = self.content_num;
	}

	/**
	 * \details helper function return an array of jsons
	 * 		store previously typed contents
	 * 		used in:
	 * 			newContent() (store before create new textarea for new content)
	 * \warning not in use right now
	 */
	temporaryStoreContent() {
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
		for (i = 1; i < self.pre_updated_content_num + 1; i++) {
		var contentArray = {
			name: document.getElementById("content-name" + i).value,
			type: document.getElementById("content-type" + i).value,
			content: document.getElementById("content" + i).value
		};
		contents.push(contentArray);
		}
	
		return contents;
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////////
//
// Helper functions for edit icon near resource name and content name
//
/////////////////////////////////////////////////////////////////////////////////////////////////

/** Helper function for changing textbox tooltip visibility
 * \brief	Used in create resource modal, edit resource modal
 * 			Used to set a tooltip's visibility to hidden
 * \warning will only hide ttSib's first sibling of class tooltip
 * 
 * @param {*} ttSib sibling of the tooltip to be hidden
 */
function hideTooltip(ttSib) 
{ 
	var tooltip = ttSib.parentNode.getElementsByClassName("tooltip")[0];
	tooltip.style.visibility = 'hidden';
}

/** Helper function for changing textbox tooltip visibility
 * \brief	Used in create resource modal, edit resource modal
 * 			Used to set a tooltip's visibility to visible
 * \warning will only show ttSib's first sibling of class tooltip
 * 
 * @param {*} ttSib sibling of the tooltip to be hidden 
 */
function showTooltip(ttSib) 
{
	var tooltip = ttSib.parentNode.getElementsByClassName("tooltip")[0];
	tooltip.style.visibility = 'visible';
}
