
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
	return d3.json(url, {method:'get'}).then(function(error, data){
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

	const csrftoken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrfToken
    });
	return d3.json(url, {method: 'post', headers, body: content}).then(function(error, data){
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
		return callBack1();
	}

	const csrftoken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrfToken
    });
	return d3.json(url, {method:'patch', headers, body: content}).then(function(error, data){
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

	return d3.json(url, {method: 'delete', headers}).then(function(error, data){
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

Server.prototype.getData = function(node, levels, handleError, handleSuccess)
{
    var self = this;
    self.treeHandleError = handleError;
    self.treeHandleSuccess = handleSuccess;
	// if any of node, levels_up, or levels_down is undefined/null, use an empty string instead
	// but allow levels to be 0
	node = node ? node : ""
	levels_up = levels_up || levels_up === 0 ? levels_up : ""
	levels_down = levels_down || levels_down === 0 ? levels_down : ""
	// what is the url for this request?
	url = "/data/topic_tree/?topic="+node+"&levels_up="+levels_up+"&levels_down="+levels_down;
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

Server.prototype.getTopicJSON = function(id, handleError, handleSuccess)
{
	var self = this;
	self.treeHandleError = handleError;
	self.treeHandleSuccess = handleSuccess;
	
	url = "/data/topic?id="+id;
	return d3.json(url)
		.then(function(data){			
				return self.handleSuccess(id, data);
			})
		
		.catch(function(error){
			console.log('Error', error.message);
		});		
}

Server.prototype.storeTopic = function(name)
{
	var self = this;
	data = {"name": name};	
	url = "/topics";
	const csrftoken = self.getCookie("XSRF-TOKEN");
	const headers = new Headers({
        'X-XSRF-TOKEN': csrftoken
    });
	fetch(url, {
		method: 'Post',
		body: JSON.stringify(data),
		headers
	}).then(function(data){			
		console.log(data);
	})

	.catch(function(error){
	console.log('Error', error.message);
	});	
}



