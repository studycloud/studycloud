
function Server(node, handleError, handleSuccess)
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;
    self.treeHandleError = handleError;
    self.treeHandleSuccess = handleSuccess;
}

Server.prototype.getData = function(node, levels)
{
    var self = this;
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
    		return self.handleError(url, error);

    	}
    	else {
    		return self.handleSuccess(data);
		}	
    });
	

};


Server.prototype.handleError = function(url, error)
{
	var self = this;
	if (error == "Error: Internal Server Error")
		return d3.json(url, function(error, data){
			if (error){
				return self.handleError(url, error);
	
			}
			else {
				return self.handleSuccess(data);
			}	
		});
	else{
		alert(error);
		throw(error);
	}
    return self.treeHandleError(url, error);



};


Server.prototype.handleSuccess = function(data)
{
    var self = this;
    return self.treeHandleSuccess(data);



};
