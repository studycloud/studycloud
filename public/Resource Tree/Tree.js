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
	
	self.setData(data);
	
	self.breadcrumbStack = [0];
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
		.force("ForceCenterX", d3.forceX(self.frame.boundary.width / 2))
		.force("ForceCenterY", d3.forceY(self.frame.boundary.height / 2));

	self.simulation
		.nodes(self.nodes.data())
		.on('tick', function(){self.draw()});

	self.simulation.force("ForceCenterX")
		.strength(0.1);
	
	self.simulation.force("ForceCenterY")
		.strength(0.1);	
		
	self.simulation
		.force("ForceLink")
			.strength(1)
			.links(self.links.data())
			.id(function(d){return d.id;})
			.distance(80);
			
	self.simulation
		.force("ForceCharge")
			.strength(-1500);

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


Tree.prototype.getParentsSelection = function(node_id)
{
	var self = this;
	
	var node_ids = new Set();
	var link_ids = new Set();
	
	
	self.links.data().forEach(function(link)
		{
			if (link.target.id == node_id)
			{	
				//we found a parent, record the id
				node_ids.add(link.source.id);
				link_ids.add(link.id);
			}
		}
	);
	
	var nodes_selection = self.nodes.filter(function()
		{
			return node_ids.has(this.getAttribute("data_id"));
		}
	);
	
	var links_selection = self.links.filter(function()
		{
			return link_ids.has(this.getAttribute("data_id"));
		}
	);
	
	return [nodes_selection, links_selection];
	
}


Tree.prototype.getNChildrenSelections = function(node_id, children_levels_num)
{
	var self = this;
	var node_ids; 
	var link_ids;
	
	[node_ids, link_ids] = self.getNChildrenIds(node_id, children_levels_num)
	
	var nodes_selection = self.nodes.filter(function()
		{
			return node_ids.has(this.getAttribute("data_id"));
		}
	);
	
	var links_selection = self.links.filter(function()
		{
			return link_ids.has(this.getAttribute("data_id"));
		}
	);
	
	return [nodes_selection, links_selection];
};


//Wrapper for getNChildrenRecurse
Tree.prototype.getNChildrenIds = function(node_id, children_levels_num)
{
	var self = this;
	
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
	var self = this;
	
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
	
	var self = this;
	
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
					.attr("r", "1%")
					.on("click", function(d){self.nodeClicked(this)});
		
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
	self.updateDataLinks(self.links, data.links);
	
	self.nodes_simulated = self.nodes;
	self.links_simulated = self.links;
	
	self.simulationRestart();
}


Tree.prototype.updateDataNChildren = function(node_id, children_levels_num, data)
{	
	var self = this;
	var nodes_updated;
	var links_updated;
	
	[nodes_updated, links_updated] = getNChildrenSelections(node_id, children_levels_num);
				
	self.updateDataNodes(nodes_updated, data.nodes);
	self.updateDataLinks(links_updated, data.links);
	
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

	self.nodes.select("circle")
		.attr('cx', function(d) { return d.x; })
		.attr('cy', function(d) { return d.y; });
};

Tree.prototype.draw = function()
{
	var self = this;
	
	self.drawNodes();
	self.drawLinks();
		
};

Tree.prototype.nodeCoordinateInterpolater = function(d)
{
	var coordinate = {};
	
	switch(d.level)
	{
		case -1:
			coordinate.x = 0;
			coordinate.y = 0;
			break;
		case 0:
			coordinate.x = self.frame.svg.center.x;
			coordinate.y = self.frame.svg.center.y;
			break;
		case 1:
		case 2:
		case 3:
			return;
	}

	var interpolate_x = d3.interpolateNumber(d.x, coordinate.x);
	var interpolate_y = d3.interpolateNumber(d.y, coordinate.y);
		
	switch(d.level)
	{
		case -1:
		case 0:				
			return function(t)
			{
				d.fx = interpolate_x(t);
				d.fy = interpolate_y(t);
				d.x = d.fx;
				d.y = d.fy;
				
				self.draw();
			};	
	}
	
	
}


Tree.prototype.centerOnNode = function(node)
{	//This function centers the tree visualization on a node.
	
	self = this;
	
	data_id = node.__data__.id;
	
	var nodes_selection_primary, links_selection_primary;
	var nodes_selection_children, links_selection_children;
	var nodes_selection_parents, links_selection_parents;
	
	//Get selections of the circle we are centering on, and the parent DOM element of that circle
	var node_selection_clicked = d3.select(node.parentNode);

	[nodes_selection_primary, links_selection_primary] = self.getNChildrenSelections(data_id, 1);
	[nodes_selection_children, links_selection_children] = self.getNChildrenSelections(data_id, 2);
	
	//Get the parent nodes of the circle
	[nodes_selection_parents, links_selection_parents] = self.getParentsSelection(data_id);

	
	self.nodes.each(function(d){d.level = 3;});
	self.links.each(function(d){d.level = 3;});
	
	nodes_selection_children.each(function(d){d.level = 2;});
	links_selection_children.each(function(d){d.level = 2;});
	
	nodes_selection_primary.each(function(d){d.level = 1});
	links_selection_primary.each(function(d){d.level = 1;});
	
	node_selection_clicked.each(function(d){d.level = 0;});
	
	nodes_selection_parents.each(function(d){d.level = -1;});
	links_selection_parents.each(function(d){d.level = -1;});
	

	//All of the nodes and links together.
	var nodes_selection = SelectionAdd(nodes_selection_children, nodes_selection_parents);
	var links_selection = SelectionAdd(links_selection_children, links_selection_parents);
	
	//Make all of the animations!
	var transition = d3.transition();
	
	transition.duration(500);
	
	self.simulation.stop();
	
	//Clear the fixed position nodes except center or parents
	self.nodes.each(function(d)
		{
			switch (d.level)
			{
				case -1:
				case 0: 
					break;
				case 1:
				case 2: 
				case 3: 
					d.fx = null;
					d.fy = null;
					break;
			}
		}
	);
	
	self.nodes
		.select("circle")
		.transition(transition)
			.attr("r", function(d)
				{
					switch (d.level)
					{
						case -1: return "15%";
						case 0: return "5%";
						case 1: return "3%";
						case 2: return "1%";
						case 3: return this.getAttribute("r");
					}
				}
			)
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
					self.simulationRestart();
					//self.simulation.stop();
					
					switch (d.level)
					{
						case -1:
						case 0: 
						case 1: 
						case 2: 
							this.style.visibility = "unset";
							break;
						case 3:
							break;
					}
				}
			)
			.on("end", function(d)
				{
					//self.simulationRestart()
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
			)
			.on("interrupt", function(){console.log("aaa");})
			.tween("coordinates", self.nodeCoordinateInterpolater);
	
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
							this.style.visibility = "unset";
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
	
	
	self.links_simulated = links_selection;
	self.nodes_simulated = nodes_selection;

	
}

Tree.prototype.BreadcrumbStackUpdate = function(id)
{
	self = this;
}

Tree.prototype.nodeClicked = function(node)
{
	self = this;
	self.centerOnNode(node);
}


tree_1 = new Tree("Blah", "tree");

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
  "links": [
    {
      "source": "t1",
      "target": "t2",
	  "id": "l1"
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
    }
  ]
};	

tree_1.setData(data);