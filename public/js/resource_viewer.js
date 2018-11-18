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

// Dummy data which I would get from the server.

var received = '{"meta": {"name": "Resource 1", "author_name": "Giselle Serate", "author_type": "teacher", "use_name":"Notes"},\
 "contents": \
 [\
 {"name": "Resource Content BROKENadfs;lj;", "type": "link", "content": "<a href=http://google.com>blahhhh</a>", "created": "date", "updated": "date"},\
 {"name": "Resource 222222", "type": "text", "content": "sadfdsflkjsfkljasklff", "created": "date", "updated": "date"}\
 ]}';
var received2 = '{"meta": {"name": "Resource 1", "author_name": "Giselle Serate", "author_type": "teacher", "use_name":"Notes"}, "contents": [ {"name": "Resource Content BROKENadfs;lj;", "type": "HECK;ijldfskj;l", "content": "<a href=http://google.com>blahhhh</a>", "created": "date", "updated": "date"}]}';
var created;
var contentNum = 0;

//var received = '{"meta": {"name": "Resource 1", "author_name": "Giselle Serate", "author_type": "teacher"}, "contents": [ {"name": "Resource Content BROKENadfs;lj;", "type": "link", "content": "http://google.com", "created": "date", "updated": "date"}]}';
/*$(document).ready(function(){ 
	
	if (document.getElementById('resource-container').className == "view"){
		resourceCreator = false;
	}
	else{
		resourceCreator = true;
	}
	//createResource();
	
	if (resourceCreator){
		createResource();
	}
	else{
		callback(received);
	}
	// you can also call:
	// error();
});*/

// Callback function that server will give the data.
function callback(received)
{
	var resource = JSON.parse(received);
	// add id = 'resource-name so we can edit the style of resource name in scss
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

// We can't find the resource requested. 
function error()
{
	document.getElementById('resource-head').innerHTML="<h1>Sorry! We don't have that resource. Would you like to write it?</h1>";
	document.getElementById('modules').innerHTML=""; // Clear modules if anything exists within it. 
}

function createResource()
{
	//create all the input to create resources
	document.getElementById('resource-head').innerHTML="<h1>Resource Creator</h1>"
	document.getElementById('modules').innerHTML = "<div class=resource-divider></div> <form> <div class 'resource-creator> Resource Name: <br> \
	<input type = 'text' id = 'meta-name'> <br> Resource Use:  <select id = 'resource-use'> <option value = '1'> Notes </option> <option value = '2'> Quiz </option> </select> \
	<div class=resource-divider></div> <br> </div> <div class = 'content-creator'> Resource Content Name: <br> \
	<input type = 'text' id = 'content-name0'> <br> \
	Content Type:  <select id = 'content-type0'> <option value = 'text'> Text </option> <option value = 'link'> Link </option> </select> <br> \
	Content: <br> <textarea rows = '5' id = 'content0'> </textarea> </div> </form> <div id = 'more-contents'> </div>\
	<div> <button type = 'button' id = 'submit-button' onclick = 'submitContent()'> Submit </button> \
	<button type = 'button' id = 'new-content-button' onclick = 'newContent()'> New Content </button> \
	<p id = 'demo'> </p></div> ";

}

//minor problem, everytime new content is pressed, if u enter stuff in the previous content, it is going to be cleared
function newContent(){
	contentNum += 1;
	document.getElementById('more-contents').innerHTML += "<div id='content-"+contentNum+"'></div>";
	document.getElementById('content-'+contentNum).innerHTML += "<div class=resource-divider></div> <br> </div> <div class = 'content-creator'> Resource Content Name: <br> \
	<input type = 'text' id = 'content-name"+contentNum+"'> <br> \
	Content Type:  <select id = 'content-type"+contentNum+"'> <option value = 'text'> Text </option> <option value = 'link'> Link </option> </select> <br> \
	Content: <br> <textarea rows = '5' id = 'content"+contentNum+"'> </textarea> </div> </form>";
}

function submitContent() 
{
	//this function gets triggered with the submit function is clicked
	//all the userinput are stored in these variables
	var resourceName = document.getElementById("meta-name").value;
	var resourceUse = document.getElementById("resource-use").value;
	var contentName = [];
	var contentType = [];
	var content = [];

	for (i=0;i < (contentNum+1); i++){
		contentName.push(document.getElementById("content-name"+i).value);
		contentType.push(document.getElementById("content-type"+i).value);
		content.push(document.getElementById("content"+i).value);
	}

	//document.getElementById("demo").innerHTML = resourceName + resourceUse + "content: " + contentName + "|" + contentType + "|" + content;
	
	var resourceArray =  {
		"name":resourceName,
		"use_id": resourceUse,
		"contents":
		[
			{
				"name": document.getElementById("content-name0").value,
				"type": document.getElementById("content-type0").value,
				"content": document.getElementById("content0").value
			}
		]
	};
	
	for (i=1;i < (contentNum+1); i++){
		var contentArray =
		{
			"name": document.getElementById("content-name"+i).value,
			"type": document.getElementById("content-type"+i).value,
			"content": document.getElementById("content"+i).value
		};
		resourceArray.contents.push(contentArray);
	}

	document.getElementById("demo").innerHTML = resourceArray.contents[1]["name"];
	//document.getElementById("demo").innerHTML = resourceArray.contents.length;

}

function resourceEditor(){
	var resource = JSON.parse(received);
	
	createResource(); //open the resource editor
	
	//load the resource into the editor
	document.getElementById("meta-name").value = resource.meta.name;
	
	if (resource.meta.use_name == "Notes"){
		document.getElementById("resource-use").selected = "1";
	}
	else if (resource.meta.use_name == "Quiz"){
		document.getElementById("resource-use").selected = "2";
	}
	
	for (i=0; i < resource.content.length; i++){
		if (i > 0){
			newContent();
		}
		document.getElementById("content-name"+0).value = resource.content[i]["name"];
	}
	
}
