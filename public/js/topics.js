server = new Server();
tree_topics = new Tree("topic", "topic-tree", server);

var data=
{
  "nodes": [
    {
      "name": "Topic Root",
      "author_id": 9,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t1"
    },
    {
      "name": "Topic A",
      "author_id": 19,
      "use_id": 1,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t2"
    },
    {
      "name": "Topic B",
      "author_id": 16,
      "use_id": 3,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t3"
    },
    {
      "name": "Topic C",
      "author_id": 50,
      "use_id": 20,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t11"
    },
    {
      "name": "Topic D",
      "author_id": 40,
      "use_id": 21,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t12"
    },
    {
      "name": "Topic E",
      "author_id": 16,
      "use_id": 22,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t13"
    },
    {
      "name": "Topic AA",
      "author_id": 17,
      "use_id": 3,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t4"
    },
    {
      "name": "Topic BA",
      "author_id": 24,
      "use_id": 1,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t5"
    },
    {
      "name": "Topic AB",
      "author_id": 2,
      "use_id": 3,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t6"
    },
    {
      "name": "Topic AC",
      "author_id": 9,
      "use_id": 3,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t7"
    },
    {
      "name": "Topic BB",
      "author_id": 15,
      "use_id": 3,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t8"
    },	
    {
      "name": "Topic BBA",
      "author_id": 3,
      "use_id": 2,
      "created_at": "2017-11-02 21:02:03",
      "updated_at": "2017-11-02 21:02:03",
      "id": "t9"
    }
  ],
  "connections": [
    {
      "source": "t1",
      "target": "t2",
	  "id": "l1"
    },
    {
      "source": "t1",
      "target": "t11",
	  "id": "l10"
    },
    {
      "source": "t1",
      "target": "t12",
	  "id": "l11"
    },
    {
      "source": "t1",
      "target": "t13",
	  "id": "l12"
    },
    {
      "source": "t1",
      "target": "t3",
	  "id": "l2"
    },
    {
      "source": "t2",
      "target": "t4",
	  "id": "l3"
    },
    {
      "source": "t3",
      "target": "t5",
	  "id": "l4"
    },
    {
      "source": "t2",
      "target": "t6",
	  "id": "l5"
    },
    {
      "source": "t2",
      "target": "t7",
	  "id": "l6"
    },
    {
      "source": "t3",
      "target": "t8",
	  "id": "l7"
    },
    {
      "source": "t8",
      "target": "t9",
	  "id": "l8"
    },
	{
      "source": "t3",
      "target": "t4",
	  "id": "l9"
    }
  ]
};	

var connections = data.connections;
	var IDNodeMap = d3.map(data.nodes, function (d) { return d.id; });

	connections.forEach(function(connection)
		{
			connection.source = IDNodeMap.get(connection.source);
			connection.target = IDNodeMap.get(connection.target);
		}
	);

tree_topics.setData(data);


tree_topics.nodeClicked(d3.select(".node").node());

//tree_topics.server.getData(0, 1, 3, tree_topics.updateDataNLevels.bind(tree_topics), function (node, url, error) { console.log(node, url, error); });
//tree_topics.setData(data);

// server_topics = new Server();

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


//same as classes.store
/*
Server.prototype.addClass = function(content, callback1, callback2)
{
	var self = this;
	var url = "/classes";
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callback1();
	}

	const csrfToken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrfToken,
		'Content-type': "applications/json; charset=UTF-8"
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
*/

Server.prototype.getClassesJSON = function(id, handleError, handleSuccess)
{
	
	var self = this;
		
	url = "/data/class?id="+id;
	return d3.json(url)
		.then(function(data){			
				return handleSuccess(id, data);
			})
		
		.catch(function(error){
			return handleError(error);			
		});		
}



Server.prototype.updateClass = function(class_id, content, handleError, handleSuccess)
{
	var self = this;
	data = {"content": content, "_method": "PATCH"};	
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

/*
Server.prototype.updateClass = function(class_id, content, callBack1, callBack2)
{
	
	var self = this;
	var url = "/classes/" + class_id;
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){

		return callBack1()

	}
	
	content['_method'] = "PATCH";

	const csrfToken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrftoken,
		'Content-type': "applications/json; charset=UTF-8",
		'X-HTTP-Method-Override': "PATCH"
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
*/


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

/*
Server.prototype.destroyClass = function(class_id, callback1, callback2)
{
	callback1
	var self = this;
	var url = "/classes/" + class_id;
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callback1();
	}

	const csrfToken = goodCookie;
	const headers = new Headers({
        'X-XSRF-TOKEN': csrftoken
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
*/


Server.prototype.attachClass = function(class_id, handleError, handleSuccess)
{
	var self = this;
	data = {"_method": "PATCH"};			
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

/*
Server.prototype.attachClass = function(class_id, content, callBack1, callBack2)
{
	var self = this;
	var url = "/classes/attach/" + class_id;
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1()
	}

	const csrfToken = goodCookie;
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
*/

Server.prototype.attachResource = function(resource_id, content, handleError, handleSuccess)
{
	var self = this;
	//data = {"_method": "PATCH"};	
	data = {"content": content, "_method": "PATCH"};		
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

/*
Server.prototype.attachResource = function(resource_id, content, callBack1, callBack2)
{
	
	var self = this;
	var url = "/resources/attach/" + resource_id;
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1()
	}

	const csrfToken = goodCookie;
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
*/

Server.prototype.detachResource = function(resource_id, content, handleError, handleSuccess)
{
	var self = this;
	//data = {"_method": "PATCH"};
	data = {"content": content, "_method": "PATCH"};
	url = "/classes/detach/" + resource_id;
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

/*
Server.prototype.detachResource = function(resource_id, content, callBack1, callBack2)
{
	
	var self = this;
	var url = "/resources/detach/" + resource_id;
	var goodCookie = self.getCookie("XSRF-TOKEN");

	if (goodCookie == ""){
		return callBack1()
	}
	const csrfToken = goodCookie;
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
*/