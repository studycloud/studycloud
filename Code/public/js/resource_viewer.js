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
var received = '{"meta": {"name": "Resource 1", "author_name": "Giselle Serate", "author_type": "teacher"}, "contents": [ {"name": "Resource Content 1", "type": "link", "content": "google.com", "created": "date", "updated": "date"}]}'

var resource = JSON.parse(received);

$(document).ready(function(){ 
	document.getElementById('resource-name').innerHTML=resource.meta.name;
	set_author();
	for(var i=0;i<1;i++)
	{
		display_content(i);
	}
});

// Set author and classes to format. 
function set_author() 
{
	// Clear all classes on the author-name field. 
	var cl=document.getElementById('author-name').classList;
	for(var i=cl.length; i>0; i--) {
	    cl.remove(cl[0]);
	}
	document.getElementById('author-name').classList.add(resource.meta.author_type);
	document.getElementById('author-name').innerHTML=resource.meta.author_name;
}

// Display one of the contents in the array.
function display_content(which)
{
	if(resource.contents[which].type="link")
	{
		document.getElementById('content-0').innerHTML="<a href="+resource.contents[which].content+">"+resource.contents[which].name+"</a>";
	}
	// Add other types as you will. 
}