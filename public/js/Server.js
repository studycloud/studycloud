
function Server()
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;
}

Server.prototype.getResource = function(resource, callBack1, callBack2)
{
	var self = this;
	
	var url = "/resource/data/" + resource;
	return d3.json(url, function(error, data)
	{
		if(error)
		{
			return callBack1();
		}
		else
		{
			return callBack2();
		}
	})
}

Server.prototype.getCookie = function(cname)
{
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

Server.prototype.addResource = function(content, callBack1, callBack2)
{
	var self = this;
	var url = "/resource/create";
	content["_method"] = "PUT";
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1();
	}

	content["X-CSRF-TOKEN"] = self.getCookie("XSRF-TOKEN");
	return d3.request(url).post(content, function(error, data){
		if(error)
		{
			return callBack1();
		}
		else
		{
			return callBack2();
		}
	});
}

Server.prototype.editResource = function(resource, content, callBack1, callBack2)
{
	var self = this;
	var url = "/resource/" + resource;
	content["_method"] = "PATCH";
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1();
	}

	content["X-CSRF-TOKEN"] = self.getCookie("XSRF-TOKEN");
	
	return d3.request(url).post(content, function(error, data){
		if(error)
		{
			return callBack1();
		}
		else
		{
			return callBack2();
		}
	});
}

Server.prototype.deleteResource = function(resource, callBack1, callBack2)
{
	var self = this;
	var url = "/resource/data/" + resource;
	return d3.json(url, function(error, data){
		if(error)
		{
			return callBack1();
		}
		else
		{
			return callBack2();
		}
	})
}

Server.prototype.getData = function (node, levels_up, levels_down, handleSuccessExternal, handleErrorExternal)
{
	console.log("Request Sent");

	var self = this;
	// if any of node, levels_up, or levels_down is undefined/null, use an empty string instead
	// but allow levels to be 0
	node = node ? node : ""

	levels_up = levels_up || levels_up === 0 ? levels_up : ""
	levels_down = levels_down || levels_down === 0 ? levels_down : ""
	// what is the url for this request?
	url = "/data/topic_tree?id=" + node + "&levels_up=" + levels_up + "&levels_down=" + levels_down;

	d3.json(url)
		.then(function (data)
			{
				console.log("Response Recieved");
				self.handleSuccess(node, levels_up, levels_down, data, handleSuccessExternal);
			},
			function (error)
			{
				console.log("Response Errored");
				self.handleError(node, levels_up, levels_down, data, handleSuccessExternal);
			}
		);
}

Server.prototype.handleError = function (node, levels_up, levels_down, data, handleSuccessExternal, handleErrorExternal, url, error)
{
	/*
	var self = this;
	if (error == "Error: Internal Server Error")
		return d3.json(url, function(error, data){
			if (error)
			{
				if(error != "Error: Internal Server Error")
				{
					return handleErrorExternal(url, error);
				}
				else
				{
					alert(error);
					throw(error);
				}
			}
			else
			{
				return self.handleSuccess(node, levels_up, levels_down, data, handleSuccessExternal);
			}
		});
	else
	{
		alert(error);
		throw(error);
	}
	return self.treeHandleError(node, url, error);
	*/
};


Server.prototype.handleSuccess = function (node, levels_up, levels_down, data, handleSuccessExternal)
{
	var self = this;

	var connections = data.connections;
	var IDNodeMap = d3.map(data.nodes, function (d) { return d.id; });

	connections.forEach(function(connection)
		{
			connection.source = IDNodeMap.get(connection.source);
			connection.target = IDNodeMap.get(connection.target);
		}
	);
	
	return handleSuccessExternal(node, levels_up, levels_down, data);
};
