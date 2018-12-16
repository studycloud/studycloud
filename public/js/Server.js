
function Server()
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;
}

Server.prototype.getResource = function(resource_id, callBack1, callBack2)
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

Server.prototype.addResource = function(content, callBack1, callBack2)
{
	var self = this;
	var url = "/resources";
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1();
	}
s
	const csrftoken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrfToken
    });
	return d3.json(url, {method: 'post', headers, body: content}).then(function(data, error){
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

Server.prototype.editResource = function(resource_id, content, callBack1, callBack2)
{
	
	var self = this;
	var url = "/resources/" + resource_id;
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1()
	}

	const csrftoken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrfToken
    });
	return d3.json(url, {method:'patch', headers, body: content}).then(function(data, error){
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

Server.prototype.deleteResource = function(resource_id, callBack1, callBack2)
{
	callback1
	var self = this;
	var url = "/resources/" + resource_id;
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1();
	}

	const csrftoken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrfToken
    });

	return d3.json(url, {method: 'delete', headers}).then(function(data, error){
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

Server.prototype.getData = function(node, levels_up, levels_down, handleError, handleSuccess)
{
	
    var self = this;
    
    
	// if any of node, levels_up, or levels_down is undefined/null, use an empty string instead
	// but allow levels to be 0
	node = node ? node : ""
	levels_up = levels_up || levels_up === 0 ? levels_up : ""
	levels_down = levels_down || levels_down === 0 ? levels_down : ""
	// what is the url for this request?
	url = "/data/topic_tree/?topic="+node+"&levels_up="+levels_up+"&levels_down="+levels_down;
    return d3.json(url, {method: 'get'}).then(function(data, error){
    	if (error){			
    		return self.handleError(url, error, handleError);
    	}
    	else {			
    		return self.handleSuccess(data, handleSuccess);
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


Server.prototype.handleSuccess = function(data, treeHandleSuccess)
{
	
	var self = this;
	if(!(typeof treeHandleSuccess === 'function'))
	{
		return data;
	}
    return treeHandleSuccess(data);
};

Server.prototype.getTopicJSON = function(id, handleError, handleSuccess)
{
	
	var self = this;
		
	url = "/data/topic?id="+id;
	return d3.json(url)
		.then(function(data){			
				return handleSuccess(id, data);
			})
		
		.catch(function(error){
			return handleError(error);			
		});		
}

Server.prototype.storeTopic = function(name, handleError, handleSuccess)
{
	var self = this;
	data = {"name": name};	
	url = "/topics";
	const csrftoken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'Post',
		body: JSON.stringify(data),
		headers: {
			'X-XSRF-TOKEN': csrftoken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);		
	}).catch(function(error){
		return handleError(error);	
	});
}

Server.prototype.updateTopic = function(name, handleError, handleSuccess)
{
	var self = this;
	data = {"name": name};	
	url = "/topics/{id}";
	const csrftoken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'Post',		
		body: JSON.stringify(data),
		headers: {
			'X-HTTP-Method-Override': 'PATCH',
			'X-XSRF-TOKEN': csrftoken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}

Server.prototype.destroyTopic = function(name, handleError, handleSuccess)
{
	var self = this;
	data = {"name": name};	
	url = "/topics/{id}";
	const csrftoken = self.getCookie("XSRF-TOKEN");
	fetch(url, {
		method: 'Post',		
		body: JSON.stringify(data),
		headers: {
			'X-HTTP-Method-Override': 'DELETE',
			'X-XSRF-TOKEN': csrftoken,
			"Content-type": "application/json; charset=UTF-8"
		}
	}).then(function(data){
		return handleSuccess(data);
	}).catch(function(error){
		return handleError(error);
	});
}
