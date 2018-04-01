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
var received = '{"meta": {"name": "Resource 1", "author_name": "Giselle Serate", "author_type": "student"}, "content": [ {"name": "Resource Content 1", "type": "link", "content": "<url>", "created_at": "date", "updated_at": "date"}]}'

var resource = JSON.parse(received);
console.log(resource.meta.name);
console.log(resource.meta.author_name);
console.log(resource.meta.author_type);
console.log(resource.content[0].name);