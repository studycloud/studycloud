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
var resource_id = 24;
var temp_content_id = 24;

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
	// create all the input to create resources
	document.getElementById('resource-head').innerHTML="<h1>Resource Editor</h1>"
	document.getElementById('modules').innerHTML = "<div class=resource-divider></div> \
	<div class 'resource-creator> Resource Name: <br> \
	<div type = 'text' id = 'meta-name' contenteditable='true'> </div> <br> \
	Resource Use:  <br>" + selectorCodeGenerator("resource-use") + "<br> \
	<div class=resource-divider></div> <br>" + selectorCodeGenerator("content-type") + " </div>\
	<div class = 'content-creator'> Resource Content Name: <br> \
	<input type = 'text' id = 'content-name0'> <br> \
	Content Type:  <select id = 'content-type0'> <option value = 'text'> Text </option> <option value = 'link'> Link </option> </select> <br> \
	Content: <br> <textarea rows = '5' id = 'content0'> </textarea> </div> <div id = 'more-contents'> </div>\
	<div> <button type = 'button' id = 'submit-button' onclick = 'submitContent()'> Submit </button> \
	<button type = 'button' id = 'new-content-button' onclick = 'newContent()'> New Content </button> \
	<p id = 'demo'> </p></div> ";
	console.log(document.getElementById('meta-name').innerHTML);
}

/** 
 * \brief fill in html for resource creator
 * \details gets called in loginmodal.js when the creator button gets clicked
 * 			TODO: need to be implemented in the tree in the future
 * @param {*} nodeId the nodeID of where the resource will be attached (actually not sure...)
 */
function createNewResource(nodeId)
{
	//create all the input to create resources
	document.getElementById('resource-head').innerHTML="<h1>Resource Creator</h1>"
	document.getElementById('modules').innerHTML = "<div class=resource-divider></div>\
	<div class = 'resource-creator'> Resource Name: <br> \
	<input type = 'text' id = 'meta-name'> <br> \
	Resource Use: <br>" + selectorCodeGenerator("resource-use") + "<br> \
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
		console.log(resource);
		// document.getElementById("meta-name").value = resource.meta.name;
		document.getElementById("meta-name").innerHTML = resource.meta.name;
		
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
	var resource_name = document.getElementById("meta-name").innerHTML;
	var resource_use = findUseOrType("resource-use-selector");

	content_num = pre_updated_content_num;
	
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
				"type": findUseOrType("content-type-selector").toLowerCase(),
				"content": document.getElementById("content0").value
			}
		]
	};
	
	// TODO: For more contents in future, type is not correct
	for (i = 1; i < (content_num+1); i++)
	{
		var content_array =
		{
			"name": document.getElementById("content-name"+i).value,
			"type": tempType,
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
		
		loadSelectedUseOrType("content-type-selector", contents[i]["type"]);
		
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


// temporary dictionary to store all the resource uses
// TODO: Optimize and link to data base later
var resourceUseDictionary = new Object();
resourceUseDictionary[1] = "Class Notes";
resourceUseDictionary[2] = "Notes";
resourceUseDictionary[3] = "Flashcards";
resourceUseDictionary[4] = "Use 4";
resourceUseDictionary[5] = "Use 5";
resourceUseDictionary[6] = "Use 6";
resourceUseDictionary[7] = "Use 7";
resourceUseDictionary[8] = "Use 8";
resourceUseDictionary[9] = "Use 9";
resourceUseDictionary[10] = "Use 10";
resourceUseDictionary[11] = "Use 11";
resourceUseDictionary[12] = "Use 12";

// temporary dictionary to store all the content types
// TODO: Optimize and link to data base later
var contentTypeDictionary = new Object();
contentTypeDictionary[1] = "Text";
contentTypeDictionary[2] = "Link";
contentTypeDictionary[3] = "Type 3";
contentTypeDictionary[4] = "Type 4";
contentTypeDictionary[5] = "Type 5";
contentTypeDictionary[6] = "Type 6";
contentTypeDictionary[7] = "Type 7";
contentTypeDictionary[8] = "Type 8";
contentTypeDictionary[9] = "Type 9";
contentTypeDictionary[10] = "Type 10";
contentTypeDictionary[11] = "Type 11";
contentTypeDictionary[12] = "Type 12";

/**
 * Helper function for Creating Resource Use/Content Type Selector
 * TODO: currently we are using a hard-coded dictionary listing all the use/resource
 * 			need a better way to store
 * \returns html code for use/type selector
 * @param {*} selectorFor String, determines if it's selector for resource use or content type
 * 							Either: "resource-use" or content-type"
 */
function selectorCodeGenerator(selectorFor) 
{
	var name = "default";
	var inputClass = "default";
	var ulId = "default-selector";
	var inputId = "";
	var dictionary = new Object();
	if (selectorFor == "resource-use") {
		name = "resource-use";
		inputClass = "use";
		ulId = "resource-use-selector";
		inputId = "";
		dictionary = resourceUseDictionary;
	}
	else if (selectorFor == "content-type") {
		name = "content-type";
		inputClass = "type";
		ulId = "content-type-selector";
		var inputId = "t";
		dictionary = contentTypeDictionary;
	}

	var html_code = "\
	<div class='use-list-scrolling-wrapper'>\
		<ul id='" + ulId +"'>";

	for (var key in dictionary) {
		html_code += "\
			<li><input type='radio' name='" + name + "' id='"+ inputId +""+ key +"'>\
				<label for='" + inputId +""+ key + "'>" + dictionary[key] + "</label></li>";
	}
	
	html_code +=  "</ul></div>";

	return html_code;
}

/**
 * Helper function for Submitting an edited resource
 * \brief 	Find the resource use id (int) or 
 * 				 the content type name (string)
 * 
 * @param {*} ulId String, determines if we finding resource use or content type
 * 			either: "resource-use-selector" or "content-type-selector"
 */
function findUseOrType(ulId) 
{
	var ul = document.getElementById(ulId);
	var listInsideUl = ul.getElementsByTagName("li");

	for (var ele of listInsideUl) {
		if (ele.getElementsByTagName("input")[0].checked == true) {
			if (ulId == "resource-use-selector") {
				return parseInt(ele.getElementsByTagName("input")[0].id);
			} 
			else if (ulId == "content-type-selector") {
				console.log(ele.getElementsByTagName("label")[0].innerHTML);
				return ele.getElementsByTagName("label")[0].innerHTML;
			}
		}
	}
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
