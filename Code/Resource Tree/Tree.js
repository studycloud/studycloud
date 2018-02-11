function Tree(type, frame_id)
{		
	//Creates a tree visualization with a type of <type> inside the DOM element <frame_id>
	
	//The job of this constructor is to set up a new tree and 
	//	allocate memory for all of the class level variables that this class uses
	
	//self is a special variable that contains a reference to the class instance itself. 
	//	This is created in every function so that we can d3 and other libraries with anonymous functions
	
	var self = this;
	
	self.debug = true;
	
	if (self.debug)
	{
		var data = {};
		
		var nodes_count = 10;
		data.nodes = new Array(nodes_count);
		
		data.links = [];
		

		for(i = 0; i < nodes_count; i++)
		{	
			data.nodes[i] = {id: (i).toString()};
			data.links.push({target:Math.floor(Math.random() * nodes_count) , source: i, id:(i).toString()});
			data.links.push({target:Math.floor(Math.random() * nodes_count) , source: i, id:(i+nodes_count).toString()});
		}

	}
	
	self.frame = d3.select("#" + frame_id);
	self.frame.boundary = self.frame.node().getBoundingClientRect();
	
	self.frame.svg = self.frame.append("svg");
	
	self.frame.svg.center = 
	{
		x: self.frame.boundary.width/2, 
		y: self.frame.boundary.height/2 
	};
	
	self.frame.svg
		.attr("class", "tree");
	
	self.nodes = self.frame.svg
		.append("g")
			.attr("class", "layer_nodes")
			.selectAll(".node")
			
	self.links = self.frame.svg
		.append("g")
			.attr("class", "layer_links")
			.selectAll(".link")
	
	self.simulationInitialize();			
	
	self.setData(data);	
}


Tree.prototype.simulationInitialize = function()
{
	var self = this
	
	self.simulation = d3.forceSimulation()
	
	self.simulation
		.alphaTarget(-1)
		.alphaDecay(0)
		.force("ForceLink", d3.forceLink())
		.force("ForceCharge", d3.forceManyBody())
		.force("ForceCenter", d3.forceCenter(self.frame.boundary.width / 2, self.frame.boundary.height / 2));

	self.simulation
		.nodes(self.nodes.data())
		.on('tick', function(){self.draw()});

	self.simulation
		.force("ForceLink")
			.links(self.links.data())
			.id(function(d){return d.id;})
			.strength(.3);
			
	self.simulation
		.force("ForceCharge")
			.strength(-10);

};

Tree.prototype.simulationRestart = function()
{
	var self = this
	
	self.simulation.nodes(self.nodes.data());
	self.simulation.force("ForceLink").links(self.links.data())
	self.simulation.restart();
};

//Wrapper for getNChildrenRecurse
Tree.prototype.getNChildren = function(node_id, children_levels_num)
{
	var self = this
	
	//These sets contain ids of elements to update when we get new data
	var node_ids_updated = new Set();
	var link_ids_updated = new Set();
	
	var node_id_valid = false;
	
	self.nodes.data().forEach(function(node)
		{
			if (node.id == node_id)
			{	
				//we found the requested node!
				node_id_valid = true;
			}
		}
	)
	
	if (!node_id_valid)
	{
		throw ("Requested node id is invalid");
	}
	else
	{
		self.getNChildrenRecurse(node_ids_updated, link_ids_updated, node_id, children_levels_num);
	}
	
	//TODO, make sure that javascript is returning a pointer and not returning a copy of the data.
	return [node_ids_updated, link_ids_updated];
	
};

//This function is a recursive function that loops through all links, 
//and gets the children the node with id:node_id
Tree.prototype.getNChildrenRecurse = function(node_ids_updated, link_ids_updated, node_id, children_levels_num)
{	
	var self = this
	
	//return early, because we have already seen this node before
	if (node_ids_updated.has(node_id))
	{
		return;
	}
	
	
	node_ids_updated.add(node_id);
	
	if (children_levels_num <= 0)
	{
		//there are no more children to find
		return;
	}
	
	
	self.links.data().forEach(function(link)
		{
			if (link.source.id == node_id)
			{	
				//we found a child, add the id of the link and recurse
				link_ids_updated.add(link.id);
				
				self.getNChildrenRecurse(node_ids_updated, link_ids_updated, link.target.id, children_levels_num - 1);
			}
		}
	);
};



Tree.prototype.updateDataNodes = function(selection, data)
{
	
	var self = this
	
	selection = selection.data(data, function(d){return d ? d.id : this.data_id; });
	
	selection
		.enter()
			.append("g")
				.attr("class", "node")
				.attr("data_id", function(d){return d.id;})
				.append("circle")
					//.attr("cx", function(){return Math.random() * self.frame.boundary.width})
					//.attr("cy", function(){return Math.random() * self.frame.boundary.height})
					.attr("fill", "blue")
					.attr("r", 5);
		
	selection
		.exit()
			.transition()
				.duration(300)
				.style("display", "none")
				.remove();
	
	self.nodes = self.frame.select(".layer_nodes").selectAll(".node");
}

Tree.prototype.updateDataLinks = function(selection, data)
{
	var self = this
	
	selection = selection.data(data, function(d){return d ? d.id : this.data_id; });
	
	selection
		.enter()
			.append("g")
				.attr("class", "link")
				.attr("data_id", function(d){return d.id;})
				.append('line');
								
	selection	
		.exit()
			.transition()
				.duration(300)
				.style("display", "none")
				.remove();
				
	self.links = self.frame.select(".layer_links").selectAll(".link");
}

Tree.prototype.setData = function(data)
{
	var self = this
	
	self.updateDataNodes(self.nodes, data.nodes);
	self.updateDataLinks(self.links, data.links);
	
	self.simulationRestart()
}


Tree.prototype.updateDataNChildren = function(node_id, children_levels_num, data)
{	
	var self = this
	var node_ids_updated;
	var link_ids_updated;
	
	[node_ids_updated, link_ids_updated] = self.getNChildren(node_id,children_levels_num)
	
	var nodes_updated = self.nodes.filter(function()
		{
			return node_ids_updated.has(this.getAttribute("data_id"));
		}
	);
	
	var links_updated = self.links.filter(function()
		{
			return link_ids_updated.has(this.getAttribute("data_id"));
		}
	);
				
	self.updateDataNodes(nodes_updated, data.nodes);
	self.updateDataLinks(links_updated, data.links);
	
	self.simulationRestart();
};




Tree.prototype.draw = function()
{
	
	var self = this
		
	self.nodes.select("circle")
		.attr('cx', function(d) { return d.x; })
		.attr('cy', function(d) { return d.y; });
	
	self.links.select("line")
		.attr('x1', function(d) { return d.source.x })
		.attr('y1', function(d) { return d.source.y  })
		.attr('x2', function(d) { return d.target.x  })
		.attr('y2', function(d) { return d.target.y  });	
};

tree_1 = new Tree("Blah", "tree");