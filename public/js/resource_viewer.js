// Data format I get:
// {"resource": {"name": "Resource 1",
// 			"author_name": "Giselle Serate",
// 			"author_type": "student"},
//  "content": [ {"name": "Resource Content 1",
//  			 "type": "link",
//  			 "content": "<url>",
//  			 "created_at": "date",
//  			 "updated_at": "date"}
//  		  ]
// }

// From https://www.w3schools.com/js/js_json_parse.asp

// var xmlhttp = new XMLHttpRequest();
// xmlhttp.onreadystatechange = function() {
//     if (this.readyState == 4 && this.status == 200) {
//         var myObj = JSON.parse(this.responseText);
//         document.getElementById("demo").innerHTML = myObj.name;
//     }
// };
// xmlhttp.open("GET", "json_demo.txt", true);
// xmlhttp.send();


// use when we have more than 1 content
var content_num = 0;
// use this to fix problem with "add new content" but not submitting
// having this will clear any unsaved changes
var pre_updated_content_num = content_num;

// TEMPORARY
// use this to interact with Server
// request resource and send resource JSON to server
var resource_id = 14;
var temp_content_id = 14;

/** 
 * \brief create server object and get resource for resource viewer
 * \details specified by resource_id (NOT IMPLEMENTED WITH TREE YET)
 * 		handleError function: error
 * 		handleSuccess function: displayResource
 */
function requestResource()
{
	var server = new Server();

	server.getResource(resource_id, error, displayResource);
}

/** 
 * \brief create server object and get resource for resource editor
 * \details specified by resource_id (NOT IMPLEMENTED WITH TREE YET)
 * 		handleError function: error
 * 		handleSuccess function: resourceEditor
 */
function editResource()
{
	var server = new Server();

	server.getResource(resource_id, error, resourceEditor);
}

/** 
 * \brief callback function after editting resource successfully
 */
function editResourceSuccess(data)
{
	console.log("editted resource")
	console.log(data);
}

/** 
 * \brief callback function after creating resource successfully
 */
function createResourceSuccess(data)
{
	console.log("created resource");
	console.log(data);
}

/** 
 * \brief error callback function for server object
 * 
 */
function error(data)
{
	console.log(data);
}

/** 
 * \brief display resources on resource viewer
 * @param {*} received a response (needs to turn into a json)
 */
function displayResource(received)
{
	/*
		received.json() gives us a Promise
		.then(function(resource){
			...
		} is an anonymous function
			resource is the json we want
	*/
	console.log(received);
	received.json().then(function(resource){
		console.log(resource);
		document.getElementById('resource-head').innerHTML = "<div><h1 id = 'resource-name'>"+resource.meta.name+"</h1>\
		<div>contributed by <div id='author-name'></div></div>";

		set_author(resource.meta.author_name, resource.meta.author_type);
		for(var i = 0; i < resource.contents.length; i++)
		{
			display_content(i, resource.contents[i]);
		}
	});
}

/** 
 * \brief display author's name and type
 * @param {*} name String, author's name
 * @param {*} type String, author's type
 */
function set_author(name, type) 
{
	// Clear all classes on the author-name field. 
	var cl = document.getElementById('author-name').classList;
	for(var i = cl.length; i > 0; i--) 
	{
	    cl.remove(cl[0]);
	}
	document.getElementById('author-name').classList.add(type);
	document.getElementById('author-name').innerHTML=name;
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
	if(element.type=="link")
	{
		document.getElementById('module-'+num).innerHTML+="<div><a href="+element.content+">"+element.name+"</a></div>";
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

/** 
 * \brief fill in html for resource editor
 * \details gets called in loginmodal.js when the editor button gets clicked
 * 			TODO: need to be implemented in the tree in the future
 */
function createResource()
{
	selectorCode = resourceUseSelection(resourceUseData); // selector code for resource use attachment
	// create all the input to create resources
	document.getElementById('resource-head').innerHTML="\
	<div id = 'resource-name' contenteditable=true> This text can be edited by the user. </div> \
	<body onload='checkEdits()'>";
	document.getElementById('modules').innerHTML = "<div class=resource-divider></div> \
	<div class 'resource-creator> Resource Name: <br> \
	<input type = 'text' id = 'meta-name'> <br> \
	Resource Use:  <br>" + selectorCode + "<br> \
	<div class=resource-divider></div> <br> </div>\
	<div class = 'content-creator'> Resource Content Name: <br> \
	<input type = 'text' id = 'content-name0'> <br> \
	Content Type:  <select id = 'content-type0'> <option value = 'text'> Text </option> <option value = 'link'> Link </option> </select> <br> \
	Content: <br> <textarea rows = '5' id = 'content0'> </textarea> </div> <div id = 'more-contents'> </div>\
	<div> <button type = 'button' id = 'submit-button' onclick = 'submitContent()'> Submit </button> \
	<button type = 'button' id = 'new-content-button' onclick = 'newContent()'> New Content </button> \
	<p id = 'demo'> </p></div> ";
	console.log(document.getElementById('resource-name').innerHTML);
}
function saveEdits()
{
	//get the editable element
	var editElem = document.getElementById("resource-head");

	//get the edited element content
	var userVersion = editElem.innerHTML;

	//save the content to local storage
	localStorage.userEdits = userVersion;

	//write a confirmation to the user
	document.getElementById("update").innerHTML="Edits saved!";
}

function checkEdits()
{
	//find out if the user has previously saved edits
	if(localStorage.userEdits!=null)
	document.getElementById("resource-head").innerHTML = localStorage.userEdits;
}

/** 
 * \brief fill in html for resource creator
 * \details gets called in loginmodal.js when the creator button gets clicked
 * 			TODO: need to be implemented in the tree in the future
 * @param {*} nodeId the nodeID of where the resource will be attached (actually not sure...)
 */
function createNewResource(nodeId)
{
	selectorCode = resourceUseSelection(resourceUseData); //selector code for resource use attachment
	//create all the input to create resources
	document.getElementById('resource-head').innerHTML="<h1>Resource Creator</h1>"
	document.getElementById('modules').innerHTML = "<div class=resource-divider></div>\
	<div class = 'resource-creator'> Resource Name: <br> \
	<input type = 'text' id = 'meta-name'> <br> \
	Resource Use: <br>" + selectorCode + "<br> \
	<div class=resource-divider></div> <br> </div> \
	<div class = 'content-creator'> Resource Content Name: <br> \
	<input type = 'text' id = 'content-name0'> <br> \
	Content Type:  <select id = 'content-type0'> <option value = 'text'> Text </option> <option value = 'link'> Link </option> </select> <br> \
	Content: <br> <textarea rows = '5' id = 'content0'> </textarea> </div> <div id = 'more-contents'> </div>\
	<div> <button type = 'button' id = 'submit-button' onclick = 'submitNewContent("+nodeId.substring(1)+")'> Submit </button> \
	<button type = 'button' id = 'new-content-button' onclick = 'newContent()'> New Content </button> \
	<p id = 'demo'> </p></div> ";
}

/** 
 * @param {*} received a response (needs to turn into a json)
 * \details load the corresponding resource in textfield 
 * 				(resource specified by resource_id)
 * 		the user has to be the author to edit
 */
function resourceEditor(received)
{
	/*
		received.json() gives us a Promise
		.then(function(resource){
			...
		} is an anonymous function
			resource is the json we want
	*/
	received.json().then(function(resource){
		document.getElementById("resource-name").innerHTML = resource.meta.name;

		// TODO: not using drop down selector anymore, using icon
		// load the resource use in the resource use drop down selector
		// display the given resource use
		$('div#select_style_text').html(resource.meta.use_name);

		// make the selector's selected value match the given resource use id
		for (i = 0; i < resourceUseData.length; i ++)
		{
			var u = resourceUseData[i];
			if (u.name == resource.meta.use_name)
			{
				$('select[name="attach"]').val(u.id);
			}
		}

		// create a text area for each content
		for (i=1; i < resource.contents.length; i++)
		{
			newContent();
		}

		loadContent(resource.contents);
	});
}

/** 
 * \brief Create a new entry area for a new content
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
 * \brief Submit an editted resource
 * \details gets triggered with the submit function is clicked in Resource Editor
 *			Create a resource JSON (w/ resource id & content id)
 *			Call the server to edit the resource
*/
function submitContent() 
{
	var resource_name = document.getElementById("resource-name").innerHTML;
	var resource_use = parseInt(document.getElementById("resource-use").value);
	var content_name_array = [];
	var content_type_array = [];
	var content_array = [];
	content_num = pre_updated_content_num;

	//get the editable element
	var editElem = document.getElementById("resource-head");
	console.log(document.getElementById("resource-name").innerHTML);

	//get the edited element content
	var userVersion = editElem.innerHTML;

	//save the content to local storage
	localStorage.userEdits = userVersion;

	for (i=0;i < (content_num+1); i++)
	{
		content_name_array.push(document.getElementById("content-name"+i).value);
		content_type_array.push(document.getElementById("content-type"+i).value);
		content_array.push(document.getElementById("content"+i).value);
	}
	
	//store all the data in json
	//NEED TO INCLUDE: resouce id, content id
	//PROBLEM: can't create additional content (this content doesn't have id)
	var resource =  
	{
		"id": resource_id,
		"name":resource_name,
		"use_id": resource_use,
		"contents":
		[
			{
				"id": temp_content_id,
				"name": document.getElementById("content-name0").value,
				"type": document.getElementById("content-type0").value,
				"content": document.getElementById("content0").value
			}
		]
	};
	
	for (i = 1; i < (content_num+1); i++)
	{
		var content_array =
		{
			"name": document.getElementById("content-name"+i).value,
			"type": document.getElementById("content-type"+i).value,
			"content": document.getElementById("content"+i).value
		};
		resource.contents.push(content_array);
	}

	console.log(resource);

	//call the server to edit the resource
	var server = new Server();
	server.editResource(resource_id, resource, error, editResourceSuccess);

	//close the content editor
	document.getElementById('my-modal').style.display = "none";
	document.getElementById('resource-head').innerHTML = " ";
	document.getElementById('modules').innerHTML = " "; //clean the display box up
}

/** 
 * \brief Submit a new resource
 * \details Gets triggered when the submit function is clicked in Resource Creator
 *			Create a resource JSON (w/ resource id & content id)
 *			Call the server to edit the resource
*/
function submitNewContent(node_id_num) 
{
	var resource_name = document.getElementById("meta-name").value;
	var resource_use = document.getElementById("resource-use").value;
	var class_id = node_id_num.toString();
	var content_name_array = [];
	var content_type_array = [];
	var content_array = [];

	for (i=0;i < (content_num+1); i++)
	{
		content_name_array.push(document.getElementById("content-name"+i).value);
		content_type_array.push(document.getElementById("content-type"+i).value);
		content_array.push(document.getElementById("content"+i).value);
	}
	
	//store all the data in json
	//PROBLEM: can only create 1 content for 1 resource
	var resource =  
	{
		"name":resource_name,
		"use_id": resource_use,
		"class_id": class_id,
		"contents":
		[
			{
				"name": document.getElementById("content-name0").value,
				"type": document.getElementById("content-type0").value,
				"content": document.getElementById("content0").value
			}
		]
	};
	
	for (i=1;i < (content_num+1); i++)
	{
		var content_array =
		{
			"name": document.getElementById("content-name"+i).value,
			"type": document.getElementById("content-type"+i).value,
			"content": document.getElementById("content"+i).value
		};
		resource.contents.push(content_array);
	}

	console.log(resource);
	
	//call the server to add resource
	var server = new Server();	
	server.addResource(resource, error, createResourceSuccess);
	
	//close the content creator
	document.getElementById('my-modal').style.display = "none";
	document.getElementById('resource-head').innerHTML = " ";
	document.getElementById('modules').innerHTML = " "; //clean the display box up
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
		document.getElementById("content-name"+i).value = contents[i]["name"];
		
		if (contents[i]["type"] == "text")
		{
			document.getElementById("content-type"+i).selectedIndex = 0;
		}
		else if (contents[i]["type"] == "link")
		{
			document.getElementById("content-type"+i).selectedIndex = 1;
		}
		
		document.getElementById("content"+i).value = contents[i]["content"];
	}
}

/** 
 * \details helper function return an array of jsons
 * 		store previously typed contents
 * 		used in:
 * 			newContent() (store before create new textarea for new content)
 */
function temporaryStoreContent()
{
	var contents = 
	[
		{
			"name": document.getElementById("content-name0").value,
			"type": document.getElementById("content-type0").value,
			"content": document.getElementById("content0").value
		}
	];

	// use pre_updated_content_num here because...
	// If the user has entries in a new content (but hasn't click submit)
	// then she clicks on "New Content" again,
	// using pre_updated_content_num will save his entries
	for (i=1;i < (pre_updated_content_num+1); i++)
	{
		var contentArray =
		{
			"name": document.getElementById("content-name"+i).value,
			"type": document.getElementById("content-type"+i).value,
			"content": document.getElementById("content"+i).value
		};
		contents.push(contentArray);
	}

	return contents;
}

/** 
 * \details clear unsaved changes
 * 		when the user close the resource editor/creator, 
 * 		reset pre_updated_content_num
 */
function resetContentNum ()
{
	pre_updated_content_num = content_num;
}

/**
 * \brief create the htmlCode to create the selector for resourceAttachment
 * @param {*} resource_use an array with each resourceUse (resource ID, resource name)
 */
function resourceUseSelection (resource_use)
{
	var html_code = "<select id = 'resource-use' name = 'attach' theme='google' width='400' style='' \
		placeholder='Select the Use of Your Resource' data-search='true'> ";
	
	for (i = 0; i < resource_use.length; i ++)
	{
		var u = resource_use[i];
		html_code += "<option value = '" + u.id + "'>" + u.name + "</option>";
	}

	html_code += "</select>";

	return html_code;
}
