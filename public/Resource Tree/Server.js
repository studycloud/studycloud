
function Server()
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;
}

Server.prototype.getResource = function(resource, callBack1, callBack2)
{
	var self = this;
	
	var url = "/data/resource/" + resource;
	return d3.json(url, function(error, data){
		if(error)
		{
			throw error;
		}
		else
		{
			return "yeah";
		}
	}
}

Server.prototype.addResource = function(resource, content, callBack1, callBack2)
{
	var self = this;
	var url = "/data/resource/" + resource;
	return d3.json(url, function(error, data){
		if(error)
		{
			throw error;
		}
		else
		{
			return "yeah";
		}
	}
}

Server.prototype.editResource = function(resource, content, callBack1, callBack2)
{
	var self = this;
	var url = "/data/resource/" + resource;
	return d3.json(url, function(error, data){
		if(error)
		{
			throw error;
		}
		else
		{
			return "yeah";
		}
	}
}

Server.prototype.deleteResource = function(resource, callBack1, callBack2)
{
	var self = this;
	var url = "/data/resource/" + resource;
	return d3.json(url, function(error, data){
		if(error)
		{
			throw error;
		}
		else
		{
			return "yeah";
		}
	}
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
