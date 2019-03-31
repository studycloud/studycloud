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

/*
Dummy Received Data, don't need to use now
var received = '{"meta": {"name": "Resource 1", "author_name": "Giselle Serate", "author_type": "teacher", "use_name":"Quiz"},\
 "contents": \
 [\
 {"name": "Resource Content BROKENadfs;lj;", "type": "link", "content": "<a href=http://google.com>blahhhh</a>", "created": "date", "updated": "date"},\
 {"name": "Resource 222222", "type": "text", "content": "sadfdsflkjsfkljasklff", "created": "date", "updated": "date"}\
 ]}';
var received2 = '{"meta": {"name": "Resource 1", "author_name": "Giselle Serate", "author_type": "teacher", "use_name":"Notes"}, "contents": [ {"name": "Resource Content BROKENadfs;lj;", "type": "HECK;ijldfskj;l", "content": "<a href=http://google.com>blahhhh</a>", "created": "date", "updated": "date"}]}';
*/

/*Dummy Data about the resource use (don't need to use this anymore)
var resourceUseData = 
[
		{
			"id": 1,
			"name": "Class Notes"
		},
		{
			"id": 2,
			"name": "Notes"
		},
		{
			"id": 3,
			"name": "Flashcards"
		},
		{
			"id": 4,
			"name": "Summary"
		},
		{
			"id": 5,
			"name": "List of Key Terms"
		},
		{
			"id": 6,
			"name": "Reading Notes"
		}
];*/

//use when we have more than 1 content
var content_num = 0;
//use this to fix problem with "add new content" but not submitting
//having this will clear any unsaved changes
var pre_updated_content_num = content_num;

//TEMPORARY
//use this to interact with Server
//request resource and send resource JSON to server
var resource_id = 24;
var temp_content_id = 24;

function requestResource()
{
	//call the server to get the JSON for resource (specified by resource_id)
	//use to display resource
	var server = new Server();

	server.getResource(resource_id, error, displayResource);
}

function editResource()
{
	//call the server to get the JSON for resource (specified by resource_id)
	//use to edit resource
	var server = new Server();

	server.getResource(resource_id, error, resourceEditor);
}

function editResourceSuccess(data)
{
	//callback 2 function for editing resources
	console.log("editted resource")
	console.log(data);
}

function createResourceSuccess(data)
{
	//callback 2 function for creating resourcess
	console.log("created resource");
	console.log(data);
}

//Error callback function, callback 1
function error(data)
{
	console.log(data);
}

// Callback function that server will give the data.
function displayResource(received)
{
	console.log(received);
	var resource = received;

	//display the received data
	document.getElementById('resource-head').innerHTML="<div><h1 id = 'resource-name'>"+resource.meta.name+"</h1><div>contributed by <div id='author-name'></div></div>";
	set_author(resource.meta.author_name, resource.meta.author_type);
	for(var i=0;i<1;i++)
	{
		display_content(i, resource.contents[i]);
	}
}

// Set author and classes to format. 
function set_author(name, type) 
{
	// Clear all classes on the author-name field. 
	var cl=document.getElementById('author-name').classList;
	for(var i=cl.length; i>0; i--) {
	    cl.remove(cl[0]);
	}
	document.getElementById('author-name').classList.add(type);
	document.getElementById('author-name').innerHTML=name;
}

// Display one of the content elements in the array.
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

function createResource()
{
	selectorCode = resourceUseSelection(resourceUseData); //selector code for resource use attachment
	//create all the input to create resources
	document.getElementById('resource-head').innerHTML="<h1>Resource Editor</h1>"
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

}

function createNewResource(nodeId)
{
	console.log(typeof(nodeId));
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


function resourceEditor(received)
{
	/*
	Allow the user to edit the resource created by himself
	Display the resourceEditor and 
	load the corresponding resourses (specified by resource_id)
	*/
	var resource = received;
	
	//load the resource into the editor
	document.getElementById("meta-name").value = resource.meta.name;

	//load the resource use in the resource use drop down selector
	//display the given resource use
	$('div#select_style_text').html(resource.meta.use_name);

	//make the selector's selected value match the given resource use id
	for (i = 0; i < resourceUseData.length; i ++){
		var u = resourceUseData[i];
		if (u.name == resource.meta.use_name){
			$('select[name="attach"]').val(u.id);
		}
	}

	for (i=1; i < resource.contents.length; i++)
	{
		newContent();
	}

	loadContent(resource.contents);
}

function newContent()
{
	//Create a new entry area for a new content
	var storedContent = temporaryStoreContent(); 
	
	//use pre_updated_content_num so if user can decide to add new content but not submit it
	//if user exit resource editor/creator, clear previous entries
	pre_updated_content_num += 1;
	document.getElementById('more-contents').innerHTML += "<div id='content-"+pre_updated_content_num+"'></div>";
	document.getElementById('content-'+pre_updated_content_num).innerHTML += "<div class=resource-divider></div> <br> </div> <div class = 'content-creator'> Resource Content Name: <br> \
	<input type = 'text' id = 'content-name"+pre_updated_content_num+"'> <br> \
	Content Type:  <select id = 'content-type"+pre_updated_content_num+"'> <option value = 'text'> Text </option> <option value = 'link'> Link </option> </select> <br> \
	Content: <br> <textarea rows = '5' id = 'content"+pre_updated_content_num+"'> </textarea> </div> </form>";

	//load the stored content back to the content textboxes
	loadContent(storedContent);
}

function submitContent() 
{
	/*
	Gets triggered with the submit function is clicked in Resource Editor
	
	Submit an editted resource
	Create a resource JSON (w/ resource id & content id)
	Call the server to edit the resource
	*/
	var resource_name = document.getElementById("meta-name").value;
	var resource_use = document.getElementById("resource-use").value;
	var content_name_array = [];
	var content_type_array = [];
	var content = [];
	content_num = pre_updated_content_num;

	for (i=0;i < (content_num+1); i++){
		content_name_array.push(document.getElementById("content-name"+i).value);
		content_type_array.push(document.getElementById("content-type"+i).value);
		content.push(document.getElementById("content"+i).value);
	}
	
	//store all the data in json
	//NEED TO INCLUDE: resouce id, content id
	//PROBLEM: can't create additional content (this content doesn't have id)
	var resource =  {
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
	
	for (i=1;i < (content_num+1); i++){
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

function submitNewContent(node_id_num) 
{
	/* 
	Gets triggered when the submit function is clicked in resource creator
	Submit a new resource
	
	Create a resource JSON and call the server to add new resource
	*/
	var resource_name = document.getElementById("meta-name").value;
	var resource_use = document.getElementById("resource-use").value;
	var class_id = node_id_num.toString();
	var content_name_array = [];
	var content_type_array = [];
	var content = [];

	for (i=0;i < (content_num+1); i++){
		content_name_array.push(document.getElementById("content-name"+i).value);
		content_type_array.push(document.getElementById("content-type"+i).value);
		content.push(document.getElementById("content"+i).value);
	}
	
	//store all the data in json
	//PROBLEM: can only create 1 content for 1 resource
	var resource =  {
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
	
	for (i=1;i < (content_num+1); i++){
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

function loadContent(contents)
{
	/*
	Helper function to load the content back into the textbox
	Used in:
	resource editor (initially loading resource)
	new content button (loading the previously typed resource back to the textbox)
	*/
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

function temporaryStoreContent()
{
	/*
	Helper function to temporarily store the content typed in the textbox into 
	an array. 
	Return this contents array to get put back to the textbox after a new content is created
	*/
	var contents = [
		{
			"name": document.getElementById("content-name0").value,
			"type": document.getElementById("content-type0").value,
			"content": document.getElementById("content0").value
		}
	];

	//use pre_updated_content_num here because...
	//If the user has entries in a new content (but hasn't click submit)
	//then he clicks on "New Content" again,
	//using pre_updated_content_num will save his entries
	for (i=1;i < (pre_updated_content_num+1); i++){
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

function resetContentNum ()
{
	//when the user close the resource editor/creator
	//reset preUpdatedContent Num
	//clear unused changes
	pre_updated_content_num = content_num;
}

function resourceUseSelection (resource_use)
{
	/*
	resourceUse: an array with each resourceUse (resource ID, resource name)

	create the htmlCode to create the selector for resourceAttachment
	*/

	var html_code = "<select id = 'resource-use' name = 'attach' theme='google' width='400' style='' \
		placeholder='Select the Use of Your Resource' data-search='true'> ";
	
  	for (i = 0; i < resource_use.length; i ++){
		var u = resource_use[i];
		html_code += "<option value = '" + u.id + "'>" + u.name + "</option>";
	}

	html_code += "</select>";

	return html_code;
}
