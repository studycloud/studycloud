
function Server(node, handleError, handleSuccess)
{
    // class Server handles communication with the server 
    // no data member needed 

    var self = this;


}

Server.prototype.getData = function(node, levels)
{
    var self = this;
    var url = "tree/data/?topic"+node+"&levels="+levels;
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