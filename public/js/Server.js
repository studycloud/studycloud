
function Server()
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;
}

Server.prototype.getResource = function(resource_id, callBack1, callBack2)
{
	var self = this;
	
	var url = "/resources/data?id=" + resource_id;
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
	var url = "/resources";
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

Server.prototype.editResource = function(resource_id, content, callBack1, callBack2)
{
	var self = this;
	var url = "/resources/" + resource_id;
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

Server.prototype.deleteResource = function(resource_id, callBack1, callBack2)
{
	var self = this;
	var url = "/resources/" + resource_id;
	content["_method"] = "DELETE";
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1();
	}

	content["X-CSRF-TOKEN"] = self.getCookie("XSRF-TOKEN");

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

Server.prototype.getData = function(node, levels, handleError, handleSuccess)
{
    var self = this;
    self.treeHandleError = handleError;
    self.treeHandleSuccess = handleSuccess;
	var url;
	if(node && levels)
		url = "/tree/data/?topic="+node+"&levels="+levels;
	else if(levels)
		url = "/tree/data/?topic=&levels="+levels;
	else if(node)
		url = "/tree/data/?topic="+node;
	else
		url = "tree/data";
    return d3.json(url, function(error, data){
    	if (error){
    		return self.handleError(node, url, error);

    	}
    	else {
    		return self.handleSuccess(node, data);
		}	
    });
	

};


Server.prototype.handleError = function(node, url, error)
{
	var self = this;
	if (error == "Error: Internal Server Error")
		return d3.json(url, function(error, data){
			if (error){
				if(error != "Error: Internal Server Error")
				{
					return self.handleError(url, error);
				}
				else
				{
					alert(error);
					throw(error);
				}
			}
			else {
				return self.handleSuccess(data);
			}
		});
	else{
		alert(error);
		throw(error);
	}
    return self.treeHandleError(node, url, error);
};


Server.prototype.handleSuccess = function(node, data)
{
    var self = this;
    return self.treeHandleSuccess(node, data);
};
