function Tree(type, frame_id, server, node_id = "t0", action = "none")
{		
	//Creates a tree visualization with a type of <type> inside the DOM element <frame_id>
	
	//The job of this constructor is to set up a new tree and 
	//	allocate memory for all of the class level variables that this class uses
	
	//self is a special variable that contains a reference to the class instance itself. 
	//	This is created in every function so that we can d3 and other libraries with anonymous functions
	
	var self = this;
	
	//Create the various DOM element groups needed by the tree
	self.frame = d3.select("#" + frame_id);
	// get the dimensions of the frame: width, height, bottom, top, left, right, etc
	self.frame.boundary = self.frame.node().getBoundingClientRect();


	// create the svg tag that will hold the visualization
	self.frame.svg = self.frame.append("svg");

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
	
	
	//Set up the right click menu
	self.menu_context = self.frame
		.append('div')
		.attr('class', 'menu_context');
	
	
	//hide the context menu after we click on a context menu item
	self.frame.on('click.menu_context', function(){self.menu_context.style('visibility', 'hidden');});
	
	self.nodes_simulated = {};
	self.links_simulated = {};
	
	self.simulationInitialize();
	
	//recenter the simulation 1/10 of second after it stops changing size. This prevents the updates from lagging out the browser
	var timeout_resize;
	d3.select(window).on("resize", function() 
		{
			clearTimeout(timeout_resize);
			timeout_resize = setTimeout(function(){console.log("2"); self.handleResize();}, 100); 
		}
	);

	// set the breadcrumb stack for use when we decide to implement it
	self.breadcrumbStack = [0];

	self.server = server;

	//Setup Local variables that we keep track of about nodes in our tree. This is necessary so that they stay persistent across data updates
	self.locals = {};

	// create d3 local objects, which will be useful when we want to set data on a DOM element later
	// create d3 local objects, which will be useful when we want to set data on a DOM element later
	self.locals.style = d3.local();
	self.locals.coordinates = d3.local();

	self.user_active_id = parseInt(document.getElementById("meta_user_active_id").getAttribute('content'));
	//console.log('Active user has ID: ' + self.user_active_id);

	self.nodes_captured = self.frame.svg
		.append("g")
			.attr("class", "layer_nodes_captured")
			.selectAll(".node_captured");

	self.menuContextNodeCreate();


	if(action !== "none" && node_id.charAt(0) !== 'r')
	{
		throw "Tree constructor attempting to edit/add non-resource node " + node_id;
	}

	if(action === "open")
	{
		self.centerAndOpen(node_id);
	}
	else if(action === "edit")
	{
		self.centerAndEdit(node_id);
	}
	else if(action === "add")
	{
		self.centerAndAdd(node_id);
	}
	else if(action !== "none")
	{
		throw "Invalid action passed to tree constructor: " + action;
	}
}

Tree.prototype.simulationInitialize = function()
{
	var self = this;

	// create a force simulation object
	self.simulation = d3.forceSimulation();

	// set properties of the force simulation
	self.simulation
		.alpha(0.5)
		.alphaTarget(-1)
		.alphaDecay(0.002)
		.force("ForceLink", d3.forceLink())
		.force("ForceCharge", d3.forceManyBody());

	// every time a tick event is fired on a node, call self.draw()
	self.simulation
		.nodes(self.nodes.data())
		.on('tick', function(){self.draw()});

	// does the id function set the data_id attribute on every link?
	self.simulation
		.force("ForceLink")
			.strength(.5)
			.links(self.links.data())
			.id(function(d){return d.id;})
			.distance(400);

	self.simulation
		.force("ForceCharge")
			.strength(-100);
};

Tree.prototype.simulationReheat = function()
{
	var self = this;
	self.simulation.restart();
	self.simulation.alpha(0.5);
};

Tree.prototype.simulationRestart = function()
{
	var self = this;
	
	self.simulation.nodes(self.nodes_simulated.data());
	self.simulation.force("ForceLink").links(self.links_simulated.data());
	
	self.simulationReheat();
};

Tree.prototype.getNLevelIds = function(node_id, levels_num)
{
	var self = this;

	//console.log("getNLevelIds on " + node_id + " for level " + levels_num);

	//These sets contain ids of elements found in our search
	var ids_retrieved = 
	{
		nodes: new Set(),
		links: new Set(),
		nodes_separate: [],
		links_separate: []
	};
	
	for (i=0; i <= Math.abs(levels_num); i++)
	{
		ids_retrieved.nodes_separate.push(new Set()); 
		ids_retrieved.links_separate.push(new Set()); 
	}
	
	self.getNLevelIdsRecurse(ids_retrieved, node_id, levels_num);

	//TODO, make sure that javascript is returning a pointer and not returning a copy of the data.
	return ids_retrieved;
};


Tree.prototype.getIDLevelMaps = function(node_id, levels_up_num, levels_down_num)
{
	//This functions returns maps from node_id to level for the tree. This essentially creates a labeled form of the tree.
	//It returns levels_up_num straight up the tree (can only be non-resources)
	//It return levels_down_num down the tree of nodes, and then one more level of resources. 
	//	If you ask for 3 levels down, you get 3 levels of nodes, and 1 more level of only resources.

	var self = this;

	var ID_Level_Map = d3.map();
	self.getIDLevelMapTraverseUp(ID_Level_Map, node_id, levels_up_num);

	//Delete the temporary root node becuase it was added while traversing up 
	//	and will cause the down traversal to terminate early.
	ID_Level_Map.remove(node_id);
	self.getIDLevelMapTraverseDown(ID_Level_Map, node_id, levels_down_num);
	//Change this so that it adds children to the visiting map.

	return ID_Level_Map;
};

Tree.prototype.getIDLevelMapTraverseUp = function(IDLevelMap, node_id, levels_up_num, levels_traversed = 0)
{
	var self = this;

	//return early, because we have already seen this node before
	if (IDLevelMap.has(node_id))
	{
		return;
	}
	
	//Add the node ID to our overall list, as well as our list for this particular level
	IDLevelMap.set(node_id, levels_traversed);
	
	if (levels_up_num === 0)
	{
		//there are no more children to find
		return;
	}
	
	//We are searching for parents, so check if our current node is a child of any nodes, and traverse upwards
	self.links.data().forEach(function (link)
		{
			if (link.target.id === node_id)
			{
				//We found a parent! 
				//A parent is any node that has a link pointing towards our node
				self.getIDLevelMapTraverseUp(IDLevelMap, link.source.id, levels_up_num - 1, levels_traversed - 1);
			}
		}
	);
};

Tree.prototype.getIDLevelMapTraverseDown = function(IDLevelMap, node_id, levels_down_num, levels_traversed = 0)
{
	var self = this;

	//return early, because we have already seen this node before
	if (IDLevelMap.has(node_id))
	{
		return;
	}
	
	//Add the node ID to our overall list, as well as our list for this particular level
	IDLevelMap.set(node_id, levels_traversed);
	
	if (levels_down_num === 0)
	{	
		//now do one pass just for resources.
		
		//We are searching for children, so check if our current node is a parent of any nodes, and traverse downwards
		self.links.data().forEach(function (link)
		{
			if (link.source.id === node_id && link.target.id[0] === 'r')
			{
				//We found a resource child!!
				//A child is any node that has a link that originates at our node
				self.getIDLevelMapTraverseDown(IDLevelMap, link.target.id, levels_down_num - 1, levels_traversed + 1);
			}
			}
		);
		
		return;
	}

	if (levels_down_num === -1)
	{
		//there are no more children to find
		return;
	}


	
	//We are searching for children, so check if our current node is a parent of any nodes, and traverse downwards
	self.links.data().forEach(function (link)
		{
			if (link.source.id === node_id)
			{
				//We found a child!
				//A child is any node that has a link that originates at our node
				self.getIDLevelMapTraverseDown(IDLevelMap, link.target.id, levels_down_num - 1, levels_traversed + 1);
			}
		}
	);
};

Tree.prototype.getNLevelIdsRecurse = function(ids_retrieved, node_id, levels_num)
{
	var self = this;
	
	// This is the current distance away from the node we started at.
	var level_relative = ids_retrieved.nodes_separate.length - Math.abs(levels_num) - 1;

	//return early, because we have already seen this node before
	if (ids_retrieved.nodes.has(node_id))
	{
		return;
	}
	
	//Add the node ID to our overall list, as well as our list for this particular level
	ids_retrieved.nodes.add(node_id);
	ids_retrieved.nodes_separate[level_relative].add(node_id);
	
	if (levels_num === 0)
	{
		//there are no more children to find
		return;
	}
	else if (levels_num < 0)
	{
		
		//We are searching for parents, so check if our current node is a child of any nodes, and traverse upwards
		self.links.data().forEach(function (link)
		{
			if (link.target.id === node_id)
			{
				//We found a parent! add the id of the link and recurse
				//A parent is any node that has a link pointing towards our node
				ids_retrieved.links.add(link.id);
				ids_retrieved.links_separate[level_relative + 1].add(link.id);
				self.getNLevelIdsRecurse(ids_retrieved, link.source.id, levels_num + 1);
			}
		}
		);
	}
	else
	{
		//console.log("levels_num: " + levels_num);
		//console.log("level_relative: " + level_relative);
		//console.log(ids_retrieved.links_separate);
		
		//We are searching for children, so check if our current node is a parent of any nodes, and traverse downwards
		self.links.each(function (link)
			{
				if (link.source.id === node_id)
				{
					//We found a child! Add the id of the link and recurse
					//A child is any node that has a link that originates at our node
					ids_retrieved.links.add(link.id);
					ids_retrieved.links_separate[level_relative + 1].add(link.id);
					self.getNLevelIdsRecurse(ids_retrieved, link.target.id, levels_num - 1);
				}
			}
		);	
	}
	
};


//This function accepts node data, and adds/updates its attributes to match the form of data that we want.
//	Right now this function just makes sure that all of the nodes have a defined coordinate
Tree.prototype.cleanDataNodes = function(data)
{
	data.forEach(function(d)
	{
		if (d.x === undefined || d.y === undefined)
		{
			console.log("Nodes Data Cleaned");
			d.x = 0;
			d.y = 0;
		}

	});
};

Tree.prototype.updateDataNodes = function(selection, data)
{

	var self = this;

	console.log("Updating node data for ", selection, " to ", data);
	

	// save coordinates because they are overwritten with undefined coordinates from the server
	self.nodes.each(function(d)
		{
			var coordinates = self.locals.coordinates.get(this);

			coordinates.x = d.x;
			coordinates.y = d.y;
			coordinates.fx = d.fx;
			coordinates.fy = d.fy;
		}
	);

	selection = selection.data(data, function(d){return d ? d.id : this.data_id; });

	// add visual components for each node
	var nodes_enter = selection
		.enter()
			.append("g")
				.classed("node", true)
				.attr("data_id", function (d) { return d.id; })
				.on("click", function(){self.nodeClicked(this);})
				.on('contextmenu', function(d, i){self.menuContextNodeOpen(this, d, i);});
	
	console.log("Added nodes: ", nodes_enter);

	nodes_enter.append("rect");
	nodes_enter.append("text");

	nodes_enter.each(function(d)
		{
			// generate our fill color based on the node id
			var random_number_generator = new Math.seedrandom(d.id);
			var color = d3.interpolateRainbow(random_number_generator());

			var style = 
			{
				labeled: false,
				level: 3,
				opacity: 0,
				visible: false,
				width: 100,
				height: 100,
				color: color,
				updated: true
			};

			var coordinates = 
			{
				x: self.frame.boundary.width/2,
				y: self.frame.boundary.height/2,
				fx: null,
				fy: null,
				x_new: null,
				x_old: null,
				y_new: null,
				y_old: null
			};

			self.locals.style.set(this, style);
			self.locals.coordinates.set(this, coordinates);
		}
	);
	
	// animate removal of the old nodes
	
	var transform_delete = d3.transform()
		.scale(0);
	
	selection
		.exit()
			.classed("node-deleted", true)
			.remove();
			//.transition()
			//	.duration(1000)
			//	.style("opacity", "0")
			//	.attr("transform", transform_delete)
			//	.remove();

	self.nodes = self.frame.select(".layer_nodes").selectAll(".node");
	
	// restore coordinates that we saved earlier in this function
	self.nodes.each(function(d)
		{
			var coordinates = self.locals.coordinates.get(this);

			d.x = coordinates.x;
			d.y = coordinates.y;
			d.fx = coordinates.fx;
			d.fy = coordinates.fy;
		}
	);
};

Tree.prototype.updateDataLinks = function(selection, data)
{
	var self = this;

	data.forEach(function (link)
		{
			link.id = link.source.id + link.target.id;
			//console.log(link.source.id);
			//console.log(link.target.id);
		}
	);

	selection = selection.data(data, function(d){return d ? d.id : this.data_id; });
	
	var links_enter = selection
		.enter()
			.append("g")
				.classed("link", true)
				.attr("data_id", function(d){return d.id;})
	
	links_enter.append("line");

	links_enter.each(function(d)
		{
			var style = 
			{
				level: undefined,
				opacity: 0,
				visible: false
			};

			self.locals.style.set(this, style);
		});
								
	selection	
		.exit()
			.select("line")
				.transition()
					.duration(1000)
					.style("stroke", "transparent");
	
	selection	
		.exit()
			.classed("link-deleted", true)
			.remove();
			//.transition()
			//	.duration(1000)
			//	.style("opacity", "0")
			//	.remove();

				
	self.links = self.frame.select(".layer_links").selectAll(".link");
};

Tree.prototype.setData = function(data)
{
	var self = this;
	
	self.updateDataNodes(self.nodes, data.nodes);
	self.updateDataLinks(self.links, data.connections);
	
	self.nodes_simulated = self.nodes;
	self.links_simulated = self.links;
	
	//self.simulationRestart();
};

Tree.prototype.updateDataNLevels = function(node_id, levels_up_num, levels_down_num, data)
{	

	var self = this;

	console.log("Updating data for N Levels with:", node_id, levels_up_num, levels_down_num, data);

	self.simulation.stop();

	var nodes_updated_map;

	//Get Sets() of Ids to update the data for
	nodes_updated_map = self.getIDLevelMaps(node_id, levels_up_num, levels_down_num);

	console.log("map:", nodes_updated_map);
	console.log("data:", data);

	var nodes_updated_selection = self.nodes.filter(function(d){return nodes_updated_map.has(d.id)});
	var links_updated_selection = self.links.filter(function(d){return nodes_updated_map.has(d.source.id) && nodes_updated_map.has(d.target.id)});

	console.log(links_updated_selection);

	self.updateDataNodes(nodes_updated_selection, data.nodes);
	self.updateDataLinks(links_updated_selection, data.connections);

	self.simulationRestart();
};


Tree.prototype.drawLinks = function()
{
	var self = this;
	
	self.links.select("line")
		.attr('x1', function(d) { return d.source.x;})
		.attr('y1', function(d) { return d.source.y;})
		.attr('x2', function(d) { return d.target.x;})
		.attr('y2', function(d) { return d.target.y;});
};

Tree.prototype.drawNodesStyle = function(transition = null)
{
	var self = this;

	if (transition === null)
	{
		transition = self.nodes.transition("style_redraw");
		transition.duration(500);
		transition.ease(d3.easeBackOut.overshoot(0.8));
	}
		
	transition
		.on("start", function(d)
			{	
				if (self.locals.style.get(this).visible)
				{
					this.style.visibility = "unset";
				}
			}
		)
		.on("end", function(d)
			{
				var style = self.locals.style.get(this);
				if (!style.visible)
				{
					this.style.visibility = "hidden";
				}
			}
		)
		.attr("opacity",function(d)
			{
				return self.locals.style.get(this).opacity;
			}
		);


	transition.select("rect")
		.attr("fill", function(d)
			{
				var style = self.locals.style.get(this);
				return style.color;
			}
		)
		.attr("width", function(d)
			{
				var style = self.locals.style.get(this);
				return style.width;
			}
		)
		.attr("height", function(d)
			{
				var style = self.locals.style.get(this);
				return style.height;
			}
		)
		.attr("rx", function(d)
			{
				var style = self.locals.style.get(this);

				if (style.level === -1)
					return 0;
				else
					return style.width/2;
			}
		);
	
	transition.select("text")
			.on("start", function(d)
				{	
					var style = self.locals.style.get(this);
					if (style.labeled)
					{
						this.style.visibility = "unset";
					}
				}
			)
			.on("end", function(d)
				{
					var style = self.locals.style.get(this);
					if (!style.labeled)
					{
						this.style.visibility = "hidden";
					}
				}
			)
			.text(function(d){return d.name;})
			.attr("opacity",function(d)
				{
					var style = self.locals.style.get(this);
					return style.labeled * style.opacity;
				}
			);
}

Tree.prototype.drawNodesPosition = function()
{
	var  self = this;

	self.nodes.attr('transform', d3.transform()
		.translate(function(d)
			{
				return [d.x, d.y];
			}	
		)
	);
			
	self.nodes.select("rect")
		.attr('transform', d3.transform()
			.translate(function(d)
				{
					var width = this.width.animVal.value;
					var height = this.height.animVal.value;
					return [-width/2, -height/2];
				}
			)
		);
};

Tree.prototype.draw = function()
{
	var self = this;
	
	self.drawNodesPosition();
	self.drawLinks();
};


Tree.prototype.nodeCoordinateInterpolatorGenerator = function(d, dom_element)
{
	var self = this;
	//create interpolate functions between where we are and where we want to be	
	var coordinates = self.locals.coordinates.get(dom_element);

	var interpolate_x = d3.interpolateNumber(coordinates.x_old, coordinates.x_new);
	var interpolate_y = d3.interpolateNumber(coordinates.y_old, coordinates.y_new);
	
	return function(p)
	{
	
		if (coordinates.x_new !== null)
		{
			d.fx = interpolate_x(p);
			d.x = d.fx;	
		}
		else
		{
			d.fx = null;
		}
			
		if (coordinates.y_new !== null)
		{
			d.fy = interpolate_y(p);
			d.y = d.fy;
		}
		else
		{
			d.fy = null;
		}
	};	
};

// Defines linkLengthInterpolatorGenerator which takes in d and returns a function 
// which takes in p and sets d.length to something given initial and final distances
Tree.prototype.linkLengthInterpolatorGenerator = function(d)
{
	var distance_initial = self.simulation.force("ForceLink").distance()(d);
	
	var distance_final;
	
    switch (self.locals.style.level.get(this))
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

};

Tree.prototype.computeTreeStyle = function(ID_Level_Map)
{
	var self = this;
	
	//Set the new level of each of the nodes in our tree

	var ancestor_index = 0;

	self.nodes
		.each(function(d)
			{
				var style = self.locals.style.get(this);
				var coordinates = self.locals.coordinates.get(this);

				var level = ID_Level_Map.get(d.id);

				//Label the node with the level computed in the ID_level_Map
				style.level = level;
				style.updated = true;	

				coordinates.fx = null;
				coordinates.fy = null;
				coordinates.x_old = d.x;
				coordinates.y_old = d.y;

				switch(level)
				{
					case 0:
						style.visible = true;
						style.opacity = 1;
						style.labeled = true;
						style.width = 200;
						style.height = style.width;

						coordinates.x_new = self.frame.boundary.width/2;
						coordinates.y_new = self.frame.boundary.height/2;

						break;

					case -1:
						style.visible = true;
						style.opacity = 1;
						style.labeled = true;

						var levels = ID_Level_Map.values();
						var ancestor_count = 0;
						for (i = 0; i < levels.length; i++)
						{
							if (levels[i] === -1)
							{
								ancestor_count++;
							}
						}

						style.width = self.frame.boundary.width / ancestor_count;
						style.height = 40;


						coordinates.x_new = style.width*ancestor_index + style.width/2;
						coordinates.y_new = style.height/2;

						ancestor_index++;

						break;

					case 1:
						style.visible = true;
						style.opacity = 1;
						style.labeled = true;
						style.width = 140;
						style.height = style.width;

						coordinates.x_new = null;
						coordinates.y_new = null;

						break;

					case 2:
						style.visible = true;
						style.opacity = 0.3;
						style.labeled = false;
						style.width = 20;
						style.height = style.width;

						coordinates.x_new = null;
						coordinates.y_new = null;
						
						break;

					default:
						style.visible = false;
						style.opacity = 0;
						style.labeled = false;
						style.width = 0;
						style.height = style.width;
						
						coordinates.x_new = null;
						coordinates.y_new = null;

						break;
				}
			}
		);
	
	self.simulation.force("ForceLink").distance(function(d)
		{
			var level = ID_Level_Map.get(d.source.id);
			switch(level)
			{
			case -1:
				return 150;
			case 0: 
				return 200;
			case 1: 
				return 80;	
			default:
				return 280;
			}
		}
	);

	self.links.each(function(d)
		{
				var style = self.locals.style.get(this);
				var level = ID_Level_Map.get(d.source.id);

				//Label the node with the level computed in the ID_level_Map
				style.level = level;
		});
	
};


Tree.prototype.centerOnNode = function(node)
{
	//This function centers the tree visualization on a node. It takes the DOM element of the node to center on.
	
	var self = this;
	
	var center_node_ID = node.__data__.id;
	
	//Get ids for nodes one level up and 2 levels down
	var ID_Level_Map = self.getIDLevelMaps(center_node_ID, 1, 2);

	self.simulation.stop();
	
	self.computeTreeStyle(ID_Level_Map);

	//Set the on click handlers
	self.nodes.on("click", function(d)
	{
		var style = self.locals.style.get(this);

		var is_resource = this.__data__.id.charAt(0) === 'r';


		// Only handle the click event if the node that we clicked on was not currently centered on, or it is a resource
		// TODO: Move all of these checks into the nodeClicked function itself. Here is not the right place to handle them
		switch (style.level)
			{
				case 0:
					if(is_resource)
					{
						self.nodeClicked(this);
					}
				break;

				case -1:
				case 1:
				case 2:
					self.nodeClicked(this);
					break;
				default:
					//self.nodeClicked(this);
					break;
			}
	});

	var transition = self.nodes.transition();
	transition.duration(500);
	transition.ease(d3.easeBackOut.overshoot(0.8));

	self.drawNodesStyle(transition)

	transition
		.tween("coordinates", function(d)
			{
				return self.nodeCoordinateInterpolatorGenerator.bind(self)(d, this);
			}
		);

	self.simulation.stop();
	
	self.nodes_simulated = self.nodes.filter(function(d)
		{
			var style = self.locals.style.get(this);
			return (style.level >= -1 && style.level <= 2);
		}	
	);
	self.links_simulated = self.links.filter(function(d)
		{
			var style = self.locals.style.get(this);
			return (style.level >= -1 && style.level <= 1);
		}	
	);

	
	self.simulationRestart();
};


Tree.prototype.BreadcrumbStackUpdate = function(id)
{
	var self = this;
};

//Event Handlers for User events in the tree

//Handle Left click on nodes
Tree.prototype.nodeClicked = function(node)
{
	var self = this;

	var node_ID = node.__data__.id;
	
	if (node_ID[0] === "t")
	{
		//This isn't a resource, so it's either a class or topic.

		//Ask the server for data on up and coming nodes:

		//Create a bound callback that accepts just the returned data;

		var callback_success = function(data_promise)
		{
			data_promise.then(function(data)
			{
				console.log("Got data from server with:", node_ID, data);

				var connections = data.connections;
				var IDNodeMap = d3.map(data.nodes, function (d) { return d.id; });

				connections.forEach(function (connection)
					{
						connection.source = IDNodeMap.get(connection.source);
						connection.target = IDNodeMap.get(connection.target);
					}
				);

				self.updateDataNLevels(node_ID, 1, 2, data);
						
				//Center on the node we clicked on
				self.centerOnNode(node);
			})
		}
		var callback_error = function(error){console.log(error)};

		self.server.getTree(node_ID.slice(1), 1, 2, callback_error, callback_success);

		//Center on the node we clicked on
		self.centerOnNode(node);
		//update url
		var newUrl = window.location.protocol + "//" + window.location.host + "/classes/" + node_ID;
		window.history.pushState("class", "class"+node_ID, newUrl);

	}
	else
	{
		self.centerAndOpen(node_ID);
		//update url
		var newUrl = window.location.protocol + "//" + window.location.host + "/resources/" + node_ID;
		window.history.pushState("viewNode", "resourceViewer"+node_ID, newUrl);
	}


};


Tree.prototype.menuContextNodeCreate = function(node, data, index)
{
	var self = this;

	self.menu_context_node_items = 
	{
		delete: 
			{
				title: 'Delete',
				icon:  'delete',
				color: 'red',
				enabled: true,
				action: null
			},
		edit: 
			{
				title: 'Edit',
				icon:  'edit',
				color: 'purple',
				enabled: true,
				action: self.ContextCenterAndEdit
			},
		add:
			{
				title: 'Add',
				icon:  'add',
				color: 'green',
				enabled: true,
				action: self.ContextCenterAndAdd
			},
		capture:
			{
				title: 'Capture',
				icon:   'playlist_add',
				color: 'blue',
				enabled: true,
				action: self.nodeCapture
			},
		move:
			{
				title: 'Move',
				icon:   'open_with',
				color: 'blue',
				enabled: true,
				action: null
			},
		attach:
			{
				title: 'Attach',
				icon:   'link',
				color: 'orange',
				enabled: true,
				action: null
			},
		detach:
			{
				title: 'Detach',
				icon:   'link_off',
				color: 'red',
				enabled: true,
				action: null
			}
	};

	var menu_context = d3.select(".menu_context");

	var list = menu_context.append('ul');

	menu_items = list.selectAll('li')
			.data(Object.values(self.menu_context_node_items))
			.enter()
				.append('li')
				.classed('item', true);

	menu_items
		.style('color', function(d)
			{
				return d.color;
			}
		);

	menu_items
		.append('i')
			.classed('material-icons', true)
			.text(function(d) 
				{
					return d.icon;
				}
			);
	menu_items
		.append('span')
		.classed('title', true)
			.text(function(d) 
				{
					return d.title;
				}
			);
};

Tree.prototype.menuContextNodeOpen = function(node, data, index)
{	
	var self = this;


	var menu_context_items = self.menu_context_node_items;

	menu_context_items.add.enabled = true;
	menu_context_items.attach.enabled = true;
	menu_context_items.capture.enabled = true;
	menu_context_items.delete.enabled = true;
	menu_context_items.detach.enabled = true;
	menu_context_items.edit.enabled = true;
	menu_context_items.move.enabled = true;

	if (self.nodes_captured.data().length === 0)
	{
		menu_context_items.attach.enabled = false;
		menu_context_items.detach.enabled = false;
		menu_context_items.move.enabled = false;
	}


	//this is pseudocode for enabling editing depending on user and logged in status. 

	if (data.author_id !== self.user_active_id)
	{
		menu_context_items.edit.enabled = false;
	}

	if(data.id.charAt(0) !== 'r')
	{
		menu_context_items.edit.enabled = false;
	}

	if(data.id.charAt(0) !== 't')
	{
		menu_context_items.add.enabled = false;
	}
	
	if (self.user_active_id === 0)
	{
		menu_context_items.add.enabled = false;
		menu_context_items.attach.enabled = false;
		menu_context_items.capture.enabled = false;
		menu_context_items.delete.enabled = false;
		menu_context_items.detach.enabled = false;
		menu_context_items.edit.enabled = false;
		menu_context_items.move.enabled = false;

	}

	menu_context_items.capture.enabled = this.frame.select(".node_captured[data_id =" + data.id +"]").empty() 
		&& menu_context_items.capture.enabled
		&& this.frame.selectAll(".node_captured").data().length < 8;
	
	var menu_context = d3.select(".menu_context");

	menu_context
			.selectAll('li')
				.data(Object.values(menu_context_items))
				.on('click', function(d) 
						{	
							d3.event.stopPropagation();

							if (d.enabled)
							{
								menu_context.style('visibility', 'hidden');
								console.log('Clicked context menu item ' + d.title);
								if(d.action !== null)
								{
									d.action.bind(self)(node, data, index);
								}
							}
						}
					)
					.on('touchstart', function(d) 
						{	
							d3.event.stopPropagation();

							if (d.enabled)
							{
								d3.event.preventDefault();
								setTimeout(function()
									{
										self.menu_context.style('visibility', 'hidden');
										if(d.action !== null)
										{
											d.action.bind(self)(node, data, index);
										}
									}, 
						
									500
								);
								console.log('Clicked context menu item' + d.title);
							}
						}
					)
					.attr('enabled', function(d)
						{
							return d.enabled;
						}	
					);

	// Check if we have enough space to render the menu
	var menu_height = menu_context.node().clientHeight;

	var page_height = window.innerHeight;

	if (page_height - d3.event.pageY + window.scrollY < menu_height)
	{
		console.log(menu_height);
		console.log(d3.event.pageY);
		menu_coordinate_y = d3.event.pageY - menu_height + 2;
	}
	else
	{
		menu_coordinate_y = d3.event.pageY - 2;
	}

	var menu_coordinate_x =  (d3.event.pageX - 2);


	self.menu_context
		.style('left', menu_coordinate_x + 'px')
		.style('top', menu_coordinate_y + 'px')
		.style('visibility', 'visible');

	d3.event.preventDefault();
};

Tree.prototype.handleResize = function()
{
	var self = this;

	self.frame.boundary = self.frame.node().getBoundingClientRect();

	self.simulationRestart();
};

Tree.prototype.captureBarRender = function(node_captured_data)
{
	var self = this;

	var nodes_captured = self.nodes_captured.data(node_captured_data);
	
	var captured_count = node_captured_data.length;
	
	var captured_padding_hor = 10;
	var captured_padding_vert = 10;
	var captured_width = 140;
	var captured_height = Math.min(self.frame.boundary.height/captured_count - captured_padding_vert, captured_width);
		
	var remove_radius = 15;
	var remove_padding = 10;
	var remove_x = (captured_width / 2) - remove_radius - remove_padding;
	var remove_y = -((captured_height / 2) - remove_radius - remove_padding);
	var remove_cross_padding = remove_radius / 3;
	

	//Enter
	nodes_captured_new = nodes_captured
		.enter()
			.append("g")
				.attr("class", "node_captured")
				.attr("data_id", function(d){return d.id;});

	nodes_captured_new
		.append("rect");

	nodes_captured_new
		.append("text");
	
	nodes_captured_new.each(function(d)
		{
			var random_number_generator = new Math.seedrandom(d.id);
			d.color = d3.interpolateRainbow(random_number_generator());
		}
	);

	nodes_captured_new.select("rect")
			.attr("fill", function(d)
			{
				return d.color;
			}
		);

	var button_remove = nodes_captured_new
		.append("g")
			.classed("button_remove", true)
			.on("click", function(d, i){self.nodeUncapture(this, d, i)})
		
	button_remove
		.append("circle")
			.attr("r", remove_radius);
		
	var button_remove_cross = button_remove.append("g");

	button_remove_cross.append("line")
		.attr("x1", - remove_radius + remove_cross_padding)
		.attr("y1", 0)
		.attr("x2", remove_radius - remove_cross_padding)
		.attr("y2", 0);

	button_remove_cross.append("line")
		.attr("x1", 0)
		.attr("y1", - remove_radius + remove_cross_padding)
		.attr("x2", 0)
		.attr("y2", remove_radius - remove_cross_padding);

	// Make '+' to 'x'
	button_remove_cross.attr("transform", d3.transform().rotate(45));


	nodes_captured	
		.exit()
			.remove();

	self.nodes_captured = self.frame.svg.selectAll(".node_captured");

	self.nodes_captured
		.attr("transform", d3.transform()
			.translate(function(d, i)
				{
					return [self.frame.boundary.width - captured_width/2 - captured_padding_hor, captured_height/2 + captured_padding_vert  + i*(captured_height + captured_padding_vert)];
				}
			)
		);

	self.nodes_captured
		.select("rect")
			.attr("width", captured_width)
			.attr("height", captured_height)
			.attr('transform', d3.transform()
				.translate(function(d)
					{
						var width = this.width.animVal.value;
						var height = this.height.animVal.value;
						return [-width/2, -height/2];
					}
				)
			);

	self.nodes_captured
		.select("text")
			.text(function(d){return d.name;});

	button_remove = 
		self.nodes_captured
			.select(".button_remove")
				.attr("transform", d3.transform().translate(remove_x, remove_y));
};



Tree.prototype.nodeCapture = function(node, data, index)
{
	var self = this;

	var node_captured_data = self.nodes_captured.data();

	d3.select(node).classed("captured", true);

	var node_found_not = this.frame.select(".node_captured[data_id =" + data.id +"]").empty();

	if (node_found_not)
	{
		node_captured_data.push(data);
	}

	self.captureBarRender(node_captured_data);
};

Tree.prototype.nodeUncapture = function(node, data, index)
{
	var self = this;

	this.frame.select(".node[data_id =" + data.id +"]").classed("captured", false);
	d3.select(node.parentNode).remove();

	self.nodes_captured = self.frame.svg.selectAll(".node_captured");

	var node_captured_data = self.nodes_captured.data();

	self.captureBarRender(node_captured_data);
};

Tree.prototype.centerAndAdd = function(node_id)
{
	var self = this;

	var node = self.nodes.filter(function(d,i){
		return d.id === node_id;
	});
	self.centerOnNode(node.nodes()[0]);//kinda not really d3-ish, but whatever
	openResourceCreator(node_id.substr(1));
};

Tree.prototype.centerAndEdit = function(node_id)
{
	var self = this;

	var node = self.nodes.filter(function(d,i){
		return d.id === node_id;
	});
	self.centerOnNode(node.nodes()[0]);//kinda not really d3-ish, but whatever
	openResourceEditor(node_id.substr(1));
	//update url
	var newUrl = window.location.protocol + "//" + window.location.host + "/resources/" + node_ID + "/edit";
	window.history.pushState("viewNode", "resourceEditor"+node_ID, newUrl);
};

Tree.prototype.centerAndOpen = function(node_id)
{
	var self = this;

	var node = self.nodes.filter(function(d,i){
		return d.id === node_id;
	});
	self.centerOnNode(node.nodes()[0]);//kinda not really d3-ish, but whatever
	openResourceViewer(node_id.substr(1));
};

//wrapper for centerAndAdd to be called from context menu
Tree.prototype.ContextCenterAndAdd = function(node, data, index)
{
	var self = this;

	var node_id = data.id;
	self.centerAndAdd(node_id);
};

//wrapper for centerAndEdit to be called from context menu
Tree.prototype.ContextCenterAndEdit = function(node, data, index)
{
	var self = this;

	var node_id = data.id;
	self.centerAndEdit(node_id);
};

//wrapper for centerAndOpen to be called from context menu
Tree.prototype.ContextCenterAndOpen = function(node, data, index)
{
	var self = this;

	var node_id = data.id;
	self.centerAndOpen(node_id);
};