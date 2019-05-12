function Permissions()
{
    // class Permissions handles user permissions
    var self = this;
	
	//get user id for use throughout
	user = document.querySelector("#auth_id");
	if(user === null)
	{
		//userID of -1 means not logged in
		self.userID = -1;
	}
	else
	{
		self.userID = user.value;
	}

}

Permissions.prototype.isSignedIn = function()
{
	var self = this;
	//check if the user id is >0
	if(self.userID > 0)
	{
		return true;
	}
	else
	{
		return false;
	}
};

Permissions.prototype.createClass = function()
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.editClass = function(classJson)
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.deleteClass = function(classJson)
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.attachClass = function(parentChildJson)
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.topicCreate = function()
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.topicEdit = function(topic)
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.topicDelete = function(topic)
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.resourceAdd = function()
{
	var self = this;
	//anyone signed in has permission
	return self.isSignedIn();
};

Permissions.prototype.isResourceAuthor = function(resource)
{
	var self = this;
	if(resource.author_id === self.userID)
	{
		return true;
	}
	else
	{
		return false;
	}
};

Permissions.prototype.resourceEdit = function(resource)
{
	var self = this;
	//permission is restricted to author
	return self.isResourceAuthor(resource);
};

Permissions.prototype.resourceDelete = function(resource)
{
	var self = this;
	//permission is restricted to author
	return self.isResourceAuthor(resource);
};

Permissions.prototype.resourceAttach = function(resource)
{
	var self = this;
	//permission is restricted to author
	return self.isResourceAuthor(resource);
};

Permissions.prototype.resourceDetach = function(resource)
{
	var self = this;
	//permission is restricted to author
	return self.isResourceAuthor(resource);
};
