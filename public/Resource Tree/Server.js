
function Server(node, handleError, handleSuccess)
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;


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
    return d3.json(url);

};


Server.prototype.handleError = function()
{
    var self = this;



};


Server.prototype.handleSuccess = function()
{
    var self = this;



};
