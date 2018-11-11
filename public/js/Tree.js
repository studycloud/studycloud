function Tree(type, frame_id, server)
{		
	//Creates a tree visualization with a type of <type> inside the DOM element <frame_id>
	
	//The job of this constructor is to set up a new tree and 
	//	allocate memory for all of the class level variables that this class uses
	
	//self is a special variable that contains a reference to the class instance itself. 
	//	This is created in every function so that we can d3 and other libraries with anonymous functions
	
	var self = this;
	
	self.debug = false;
	
	if (self.debug)
	{
		var data = {};
		
		var nodes_count = 10;
		data.nodes = new Array(nodes_count);
		
		data.connections = [];
		

		for(i = 0; i < nodes_count; i++)
		{	
			data.nodes[i] = {id: (i).toString()};
			data.connections.push({target:Math.floor(Math.random() * nodes_count) , source: i, id:(i).toString()});
			data.connections.push({target:Math.floor(Math.random() * nodes_count) , source: i, id:(i+nodes_count).toString()});
		}

	}
	
	self.frame = d3.select("#" + frame_id);
	self.frame.boundary = self.frame.node().getBoundingClientRect();
	
	self.frame.on("resize", self.resizeFrame);
	
	self.frame.svg = self.frame.append("svg");
	
	self.frame.center = 
	{
		x: self.frame.boundary.width/2, 
		y: self.frame.boundary.height/2 
	};
	
	self.frame.svg
		.attr("class", "tree");
	
	
	self.links = self.frame.svg
		.append("g")
			.attr("class", "layer_links")
			.selectAll(".link");
	
	self.nodes = self.frame.svg
		.append("g")
			.attr("class", "layer_nodes")
			.selectAll(".node");
			
	self.nodes_simulated = {};
	self.links_simulated = {};
	
	self.simulationInitialize();			
	
	//if (self.debug) self.setData(data);
	
	self.breadcrumbStack = [0];

	self.server = server;

}


Tree.prototype.simulationInitialize = function()
{
	var self = this;
	
	self.simulation = d3.forceSimulation();
	
	self.simulation
		.alphaTarget(-1)
		.alphaDecay(0.01)
		.force("ForceLink", d3.forceLink())
		.force("ForceCharge", d3.forceManyBody())
		.force("ForceCenterX", d3.forceX(self.frame.center.x))
		.force("ForceCenterY", d3.forceY(self.frame.center.y));

	self.simulation
		.nodes(self.nodes.data())
		.on('tick', function(){self.draw()});

	self.simulation.force("ForceCenterX")
		.strength(0.01);
	
	self.simulation.force("ForceCenterY")
		.strength(0.01);	
		
	self.simulation
		.force("ForceLink")
			.strength(0.6)
			.links(self.links.data())
			.id(function(d){return d.id;})
			.distance(400);
			
	self.simulation
		.force("ForceCharge")
			.strength(-500);

};

Tree.prototype.simulationReheat = function()
{
	var self = this;
	self.simulation.restart();
	self.simulation.alpha(1);
};

Tree.prototype.simulationRestart = function()
{
	var self = this;
	
	self.simulation.nodes(self.nodes_simulated.data());
	self.simulation.force("ForceLink").links(self.links_simulated.data())
	
	self.simulationReheat();
};


Tree.prototype.getNLevelIds = function(node_id, levels_num, node_ids_retrieved = null, link_ids_retrieved = null)
{
	var self = this;

	//console.log("getNLevelIds on " + node_id + " for level " + levels_num);

	//These sets contain ids of elements to update when we get new data. Only create new ones if we aren't already passed a set

	if (node_ids_retrieved == null)
	{
		node_ids_retrieved = new Set();
	}

	if (link_ids_retrieved == null)
	{
		link_ids_retrieved = new Set();

	}

	var node_id_valid = false;

	self.getNLevelIdsRecurse(node_ids_retrieved, link_ids_retrieved, node_id, levels_num);

	//TODO, make sure that javascript is returning a pointer and not returning a copy of the data.
	return [node_ids_retrieved, link_ids_retrieved];
};

Tree.prototype.getNLevelIdsRecurse = function(node_ids_retrieved, link_ids_retrieved, node_id, levels_num)
{
	var self = this;
	
	//return early, because we have already seen this node before
	if (node_ids_retrieved.has(node_id))
	{
		return;
	}
	
	
	node_ids_retrieved.add(node_id);
	
	if (levels_num == 0)
	{
		//there are no more children to find
		return;
	}
	else if (levels_num < 0)
	{
		//We are searching for parents, so check if our current node is a child of any nodes, and traverse upwards
		self.links.data().forEach(function (link)
		{
			if (link.target.id == node_id)
			{
				//We found a parent! add the id of the link and recurse
				//A parent is any node that has a link pointing towards our node
				link_ids_retrieved.add(link.id);

				self.getNLevelIdsRecurse(node_ids_retrieved, link_ids_retrieved, link.source.id, levels_num + 1);
			}
		}
		);
	}
	else
	{
		//We are searching for children, so check if our current node is a parent of any nodes, and traverse downwards
		self.links.each(function (link)
			{
				if (link.source.id == node_id)
				{
					//We found a child! Add the id of the link and recurse
					//A child is any node that has a link that originates at our node
					link_ids_retrieved.add(link.id);
					self.getNLevelIdsRecurse(node_ids_retrieved, link_ids_retrieved, link.target.id, levels_num - 1);
				}
			}
		);
	}
};

Tree.prototype.getNLevelSelections = function(node_id, levels_num)
{
	var self = this;
	var node_ids; 
	var link_ids;

	[node_ids, link_ids] = self.getNLevelIds(node_id, levels_num)

	//console.log(node_ids);

	var nodes_selection = filterSelectionsByIDs(self.nodes, node_ids, "data_id");
	var links_selection = filterSelectionsByIDs(self.links, link_ids, "data_id");
	
	return [nodes_selection, links_selection];
};


//This function accepts node data, and adds/updates its attributes to match the form of data that we want.
//	Right now this function just makes sure that all of the nodes have a defined coordinate
Tree.prototype.cleanDataNodes = function(data)
{
	data.forEach(function(d)
	{
		if (d.x == undefined || d.y == undefined)
		{
			console.log("foooo");
			d.x = 0;
			d.y = 0;
		}

	});
};

Tree.prototype.updateDataNodes = function(selection, data)
{
	var self = this;
	
	console.log("Updating node data for ", selection, " to ", data);
	
	self.cleanDataNodes(data);

	selection = selection.data(data, function(d){return d ? d.id : this.data_id; });

	var nodes = selection
		.enter()
			.append("g")
			.attr("class", "node")
			.attr("data_id", function (d) { return d.id; })
			.on("click", function(d){self.nodeClicked(this)});


	nodes
		.append("circle")
			.attr("fill", function(d)
			{
				//generate our fill color based on the date created, author, and name
				var random_number_generator = new Math.seedrandom(d.id);
				var color = d3.interpolateRainbow(random_number_generator());
				return color;
			}
			)
			.attr("r", "1%")
	
	nodes
		.append("text")
			.attr("text-anchor", "middle")
			.attr("fill", "white")
			.attr("stroke", "black")
			.attr("stroke-width", "0.02em")
			.attr("font-size", "22")
			.attr("font-family", "sans-serif")
			.attr("font-weight", "bold")
			.text(function(d){return d.name;});
		
	selection	
		.exit()
			.select("circle")
				.transition()
					.duration(1000)
					.attr("r", "0%");
	
	selection
		.exit()
			.attr("class", "node-deleted")
			.transition()
				.duration(1000)
				.style("opacity", "0")
				.remove();
	
	self.nodes = self.frame.select(".layer_nodes").selectAll(".node");
};

Tree.prototype.updateDataLinks = function(selection, data)
{
	var self = this;

	data.forEach(function (link)
		{
			link.id = link.source.id + link.target.id;
			console.log(link.source.id);
			console.log(link.target.id);
		}
	);

	selection = selection.data(data, function(d){return d ? d.id : this.data_id; });
	
	selection
		.enter()
			.append("g")
				.attr("class", "link")
				.attr("data_id", function(d){return d.id;})
				.append('line');
								
	selection	
		.exit()
			.select("line")
				.transition()
					.duration(1000)
					.style("stroke", "transparent");
	
	selection	
		.exit()
			.attr("class", "link-deleted")
			.transition()
				.duration(1000)
				.style("opacity", "0")
				.remove();

				
	self.links = self.frame.select(".layer_links").selectAll(".link");
};

Tree.prototype.setData = function(data)
{
	var self = this;
	
	self.updateDataNodes(self.nodes, data.nodes);
	self.updateDataLinks(self.links, data.connections);
	
	self.nodes_simulated = self.nodes;
	self.links_simulated = self.links;
	
	self.simulationRestart();
}

Tree.prototype.updateDataNLevels = function(node_id, levels_num_children, levels_num_parents, data)
{	

	var self = this;

	console.log("Updating data for N Levels with:", data);

	var nodes_updated_ids;
	var links_updated_ids;

	//Get Sets() of Ids to update the data for
	[nodes_updated_ids, links_updated_ids] = self.getNLevelIds(node_id, levels_num_children);
	[nodes_updated_ids, links_updated_ids] = self.getNLevelIds(node_id, levels_num_parents, nodes_updated_ids, links_updated_ids);

	console.log(nodes_updated_ids);

	//Convert those ID Set()s into D3 selections
	var nodes_updated_selection = filterSelectionsByIDs(self.nodes, nodes_updated_ids, "data_id");
	var links_updated_selection = filterSelectionsByIDs(self.links, links_updated_ids, "data_id");

	self.updateDataNodes(nodes_updated_selection, data.nodes);
	self.updateDataLinks(links_updated_selection, data.connections);

	self.simulationRestart();
};


Tree.prototype.drawLinks = function()
{
	var self = this;
	
	self.links.select("line")
		.attr('x1', function(d) { return d.source.x })
		.attr('y1', function(d) { return d.source.y })
		.attr('x2', function(d) { return d.target.x })
		.attr('y2', function(d) { return d.target.y });
};

Tree.prototype.drawNodes = function()
{
	var self = this;

	self.nodes
		.attr('transform', function(d) 
			{
				return "translate(" + d.x + "," + d.y + ")";
			}
		);
	
};

Tree.prototype.draw = function()
{
	var self = this;
	
	self.drawNodes();
	self.drawLinks();
};


Tree.prototype.nodeCoordinateInterpolatorGenerator = function(d, )
{
	//create interpolate functions between where we are and where we want to be

	//console.log("Origin: (" + d.x + "," + d.y + ") Destination: (" + d.fx + "," + d.fy + ")")
	
	var interpolate_x = d3.interpolateNumber(d.x_old, d.x_new);
	var interpolate_y = d3.interpolateNumber(d.y_old, d.y_new);
	
	return function(p)
	{
		if (d.x_new != null)
		{
			d.fx = interpolate_x(p);
		}
		else
		{
			d.fx = null;
		}
			
		if (d.x_new != null)
		{
			d.fy = interpolate_y(p);
		}
		else
		{
			d.fy = null;
		}
	};	
}

// Defines linkLengthInterpolatorGenerator which takes in d and returns a function 
// which takes in p and sets d.length to something given initial and final distances
Tree.prototype.linkLengthInterpolatorGenerator = function(d)
{
	var distance_initial = self.simulation.force("ForceLink").distance()(d);
	
	var distance_final;
	
	switch(d.level)
	{
	case -1:
		distance_final = 530;
		break;
	case 1: 
		distance_final = 280; 
		break;
	case 2: 
		distance_final = 40;
		break;
	default:
		distance_final = distance_initial;
		break;
	}
	
	//console.log(d.id);
	//console.log(d.level);
	//console.log(distance_initial);
	//console.log(distance_final);
	
	
	var distance_interpolator = d3.interpolateNumber(distance_initial, distance_final);
	
	function linkInterpolator(p)
	{
		d.length = distance_interpolator(p);
	}
	
	return linkInterpolator;

}
	
Tree.prototype.centerOnNode = function (node)
{
	//This function centers the tree visualization on a node.
	
	self = this;
	
	data_id = node.__data__.id;

	var nodes_selection_children, links_selection_children;
	var nodes_selection_grandchildren, links_selection_grandchildren;
	var nodes_selection_parents, links_selection_parents;
	
	//Get selections of the circle we are centering on, and the parent DOM element of that circle
	var node_selection_clicked = d3.select(node);

	[nodes_selection_children, links_selection_children] = self.getNLevelSelections(data_id, 1);
	[nodes_selection_grandchildren, links_selection_grandchildren] = self.getNLevelSelections(data_id, 2);

	//console.log(links_selection_children);
	
	//Get the parent nodes of the circle
	[nodes_selection_parents, links_selection_parents] = self.getNLevelSelections(data_id, -1);
	
	
	//Set the new level of each of the nodes in our tree
	self.nodes.each(function(d){d.level = 3;});
	self.links.each(function(d){d.level = 3;});
	
	nodes_selection_grandchildren.each(function(d){d.level = 2;});
	links_selection_grandchildren.each(function(d){d.level = 2;});
	
	nodes_selection_children.each(function(d){d.level = 1});
	links_selection_children.each(function(d){d.level = 1;});

	nodes_selection_parents.each(function (d) { d.level = -1; });
	links_selection_parents.each(function (d) { d.level = -1; });
	
	node_selection_clicked.each(function(d){d.level = 0;});

	//	console.log(nodes_selection_parents);
	

	//All of the nodes and links together.
	var nodes_selection = SelectionAdd(nodes_selection_grandchildren, nodes_selection_parents);
	var links_selection = SelectionAdd(links_selection_grandchildren, links_selection_parents);
	
	

	//Make all of the animations!
	var transition = d3.transition();

	transition.duration(500);
	transition.ease(d3.easeBackOut.overshoot(0.8));

	self.simulation.stop();
	
	//Set the on click handlers
	self.nodes.on("click", function(d)
	{
		switch (d.level)
			{
				case -1:
				case 1: 
					self.nodeClicked(this)
				default:
					//self.nodeClicked(this)
					break;
			}
	});
	
	//Set the animatable attributes for all of the nodes that we are about to animate
	self.nodes.each(function(d, i)
		{
			var node = d3.select(this);
			//Determine our attributes based on the node level that we previously set
			switch (d.level)
			{
				case -1:
					node.classed("node-parent", true);
					d.visible = true;
					d.labeled = true;
					d.radius = "15%";
					d.opacity = 1;
					d.x_new = 150*i;
					d.y_new = 50;
					break;
				case 0:
					node.classed("node-center", true);
					d.visible = true;
					d.labeled = true;
					d.radius = "8%";
					d.opacity = 1;
					d.x_new = self.frame.center.x;
					d.y_new = self.frame.center.y;
					break;
				case 1:
					node.classed("node-child", true);
					d.visible = true;
					d.labeled = true;
					d.radius = "5%";
					d.opacity = 1;
					d.x_new = null; //Let them move
					d.y_new = null;
					d.fx = null;
					d.fy = null;
					break;
				case 2:
					node.classed("node-grandchild", true);
					d.visible = true;
					d.labeled = false;
					d.radius = "1%";
					d.opacity = 0.5;
					d.x_new = null; //Let them move
					d.y_new = null;
					d.fx = null;
					d.fy = null;
					break;
				case 3:
					node.classed("node-other", true);
					d.visible = false;
					d.labeled = false;
					d.radius = "1%";
					d.opacity = 0;
					d.x_new = null;
					d.y_new = null;
					d.fx = null;
					d.fy = null;
					break;					
			}
		
		//Set our old position to be our current, so that we can later use this in our animation
		d.x_old = d.x;
		d.y_old = d.y;
			
		}
	);
	
	
	self.nodes.select("circle")
		.transition(transition)
			.attr("r", function(d)
				{
					return d.radius;
				}
			);
	self.nodes.select("text")
		.transition(transition)
			.on("start", function(d)
				{	
					if (d.labeled)
					{
						this.style.visibility = "unset";
					}
				}
			)
			.on("end", function(d)
				{
					if (!d.labeled)
					{
						this.style.visibility = "hidden";
					}
				}
			)
			.attr("opacity",function(d)
				{
					return d.opacity;
				}
			);
			
	self.nodes
		.transition(transition)
			.on("start", function(d)
				{	
					if (d.visible)
					{
						this.style.visibility = "unset";
					}
				}
			)
			.on("end", function(d)
				{
					if (!d.visible)
					{
						this.style.visibility = "hidden";
					}
				}
			)
			.on("interrupt", function(d)
				{	
					//we got interrupted, clear the fixed position of the circles so that they animate correctly
					//d.fx = null;
					//d.fy = null;
					console.log("Animation Interrupted");
				}
			)
			.attr("r", function(d)
				{
					return d.radius;
				}
			)
			.attr("opacity",function(d)
				{
					return d.opacity;
				}
			)
			.tween("coordinates", self.nodeCoordinateInterpolatorGenerator);
	

	
	self.simulation.force("ForceLink").distance(function(d)
		{
			switch(d.level)
			{
			case -1:
				return 150;
			case 1: 
				return 150; 
			case 2: 
				return 25;	
			default:
				return 280;
			}
		}
	);
	
		/*
	self.links
		.transition(transition)
			.tween("linkLength", self.linkLengthInterpolatorGenerator);
	
	self.frame.svg
		.transition(transition)
			.tween("UpdateLinkDistance", function()
				{
					return function()
					{
						//console.log("hi");
						self.draw();
						self.simulation.force("ForceLink").distance(function(d){return d.length;});
					}
				}
			)
			.on("end", function(d)
				{
					self.simulationRestart();
				}
			);
	
	*/
	
	//Not animating links since they aren't shown now
	/*
	self.links
		.transition(transition)
			.style("opacity", function(d)
				{
					switch (d.level)
					{
						case -1:
						case 0: 
						case 1: 
						case 2: return 1;
						case 3: return 0;
					}
				}
			)
			.on("start", function(d)
				{
					switch (d.level)
					{
						case -1:
						case 0: 
						case 1: 
						case 2: 
							this.style.visibility = "hidden";
							break;
						case 3:
							break;
					}
				}
			)
			.on("end", function(d)
				{
					switch (d.level)
					{
						case -1:
						case 0: 
						case 1: 
						case 2: 
							break;
						case 3:
							this.style.visibility = "hidden";
							break;
					}
				}
			);
	*/
	

	
	self.links_simulated = links_selection;
	self.nodes_simulated = nodes_selection;
	
	//console.log(links_simulated);

	self.simulationRestart();
}


Tree.prototype.BreadcrumbStackUpdate = function(id)
{
	self = this;
}

Tree.prototype.nodeClicked = function(node)
{
	self = this;
	//self.server.getData(data_id, 1, 2, self.updateDataNLevels.bind(self), function (){});
	self.centerOnNode(node);
}