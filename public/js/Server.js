
function Server()
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;
}

Server.prototype.getResource = function(resource_id, callback1, callback2)
{
	var self = this;
	
	var url = "/data/resource?id=" + resource_id;
	return d3.json(url, {method:'get'}).then(function(data, error){
		if(error)
		{
			if(typeof callback1 === 'function')
			{
				return callback1(error);
			}
			else
			{
				throw error;
			}
		}
		else
		{
			if(typeof callback2 === 'function')
			{
				return callback2(data);
			}
			else
			{
				return data;
			}
		}
	});
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


Server.prototype.addResource = function(content, handleError, handleSuccess)
{
	var self = this;		
	var url = "/resources";
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(content),
		headers: {
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		
		return handleSuccess(data);		
	}).catch(function(error){
		
		return handleError(error);	
	});
}

Server.prototype.editResource = function(id, content, handleError, handleSuccess)
{
	var self = this;
	content["_method"] = "PATCH";
	data = content;
	url = "/resources/" + id;	
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',		
		body: JSON.stringify(data),
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){		
		return handleSuccess(data);		
	}).catch(function(error){		
		return handleError(error);
	});
}


Server.prototype.destroyResource = function(id, handleError, handleSuccess)
{
	var self = this;
	data = {"_method": "DELETE"};			
	url = "/resources/" + id;
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(data),			
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}

Server.prototype.getData = function(node, levels_up, levels_down, handleError, handleSuccess)
{
	
    var self = this;    
	// if any of node, levels_up, or levels_down is undefined/null, use an empty string instead
	// but allow levels to be 0
	node = node ? node : ""
	levels_up = levels_up || levels_up === 0 ? levels_up : ""
	levels_down = levels_down || levels_down === 0 ? levels_down : ""
	// what is the url for this request?
	url = "/data/topic_tree?id="+node+"&levels_up="+levels_up+"&levels_down="+levels_down;
    return d3.json(url, {method: 'get'}).then(function(data, error){
    	if (error){			
    		return self.handleError(url, error, handleError);
    	}
    	else {			
    		return self.handleSuccess(node,data,handleSuccess);
		}	
    });
};


Server.prototype.handleError = function(url, error, treeHandleError)
{
	
	var self = this;
	if(!(typeof treeHandleError === 'function')){

		return(error);
	}
	if (error == "Error: Internal Server Error")
		return d3.json(url, {method: 'get'}).then(function(data, error){
			if (error){
				if(error != "Error: Internal Server Error")
				{					
					throw(error);
				}
				else
				{					
					return treeHandleError(error);
					
				}
			}
			else {				
				return self.handleSuccess(data);
			}
		});
	else{		
		return treeHandleError(error);
	}
    
};


Server.prototype.handleSuccess = function(node, data, treeHandleSuccess)
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
	
	return treeHandleSuccess(node, data);
};

Server.prototype.getTopicJSON = function(id, handleError, handleSuccess)
{	
	var self = this;
		
	url = "/data/topic?id="+id;
	return d3.json(url)
		.then(function(data){			
				return handleSuccess(data);
			})
		
		.catch(function(error){
			return handleError(error);			
		});		
}

Server.prototype.addTopic = function(content, handleError, handleSuccess)
{
	var self = this;
	data = {"content": content};	
	url = "/topics";
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(data),
		headers: {
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		
		return handleSuccess(data);		
	}).catch(function(error){
		
		return handleError(error);	
	});
}


Server.prototype.updateTopic = function(id, content, handleError, handleSuccess)
{
	var self = this;
	content["_method"] = "PATCH";
	data = content;	
	url = "/topics/" + id;
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',		
		body: JSON.stringify(data),
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){		
		return handleSuccess(data);		
	}).catch(function(error){		
		return handleError(error);
	});
}

Server.prototype.destroyTopic = function(id, handleError, handleSuccess)
{
	var self = this;
	data = {"_method": "DELETE"};			
	url = "/topics/" + id;
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(data),			
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}

Server.prototype.addClass = function(content, handleError, handleSuccess)
{
	var self = this;		
	var url = "/classes";
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(content),
		headers: {
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		
		return handleSuccess(data);		
	}).catch(function(error){
		
		return handleError(error);	
	});
}

Server.prototype.getClassesJSON = function(class_id, handleError, handleSuccess)
{	
	var self = this;		
	url = "/data/class?id=" + class_id;	
	fetch(url, {
		method: 'get',			
		headers: {	
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){		
		return handleSuccess(data);		
	}).catch(function(error){		
		return handleError(error);
	});
}	


Server.prototype.updateClass = function(class_id, content, handleError, handleSuccess)
{
	var self = this;
	content["_method"] = "PATCH";
	data = content;	
	url = "/classes/" + class_id;
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',		
		body: JSON.stringify(data),
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){		
		return handleSuccess(data);		
	}).catch(function(error){		
		return handleError(error);
	});
}

Server.prototype.destroyClass = function(class_id, handleError, handleSuccess)
{
	var self = this;
	data = {"_method": "DELETE"};			
	url = "/classes/" + class_id;
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(data),			
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}

Server.prototype.attachClass = function(class_id, data, handleError, handleSuccess)
{
	var self = this;
	data["_method"] = "PATCH";
	url = "/classes/attach/" + class_id;	
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(data),			
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}

Server.prototype.attachResource = function(resource_id, content, handleError, handleSuccess)
{
	var self = this;
	content["_method"] = "PATCH";
	data = content;		
	url = "/resources/attach/" + resource_id;
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(data),			
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}

Server.prototype.detachResource = function(resource_id, content, handleError, handleSuccess)
{
	var self = this;
	content["_method"] = "PATCH";
	data = content;
	url = "/resources/detach/" + resource_id;
	const csrfToken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'post',
		body: JSON.stringify(data),			
		headers: {			
			'X-XSRF-TOKEN': csrfToken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}

Server.prototype.getTree = function(id, levels_up, levels_down, handleError, handleSuccess)
{
	var self = this;
	id = id ? id : ""
	levels_up = levels_up || levels_up === 0 ? levels_up : ""
	levels_down = levels_down || levels_down === 0 ? levels_down : ""		
	url = "/data/class_tree?id=" + id + "&levels_up=" + levels_up + "&levels_down=" + levels_down +id;
	return d3.json(url)
		.then(function(data){			
				return handleSuccess(id, data);
			})		
		.catch(function(error){
			return handleError(error);			
		});		
}


