function Tree(type, frame_id, server)
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
	self.frame.on('click.menu_context', function(){self.menu_context.style('display', 'none');});
	
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
		.force("ForceCharge", d3.forceManyBody())
		.force("ForceCenterX", d3.forceX(self.frame.boundary.width/2))
		.force("ForceCenterY", d3.forceY(self.frame.boundary.height/2));

	// every time a tick event is fired on a node, call self.draw()
	self.simulation
		.nodes(self.nodes.data())
		.on('tick', function(){self.draw()});

	self.simulation.force("ForceCenterX")
		.strength(0.11);

	self.simulation.force("ForceCenterY")
		.strength(0.11);	

	// does the id function set the data_id attribute on every link?
	self.simulation
		.force("ForceLink")
			.strength(1)
			.links(self.links.data())
			.id(function(d){return d.id;})
			.distance(400);

	self.simulation
		.force("ForceCharge")
			.strength(-1000);
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
			coordinates.x = d.y;
			coordinates.fx = d.fx;
			coordinates.fy = d.fy;
		}
	);

	selection = selection.data(data, function(d){return d ? d.id : this.data_id; });

	// add visual components for each node
	var nodes = selection
		.enter()
			.append("g")
				.attr("class", "node")
				.attr("data_id", function (d) { return d.id; })
				.on("click", function(){self.nodeClicked(this);})
				.on('contextmenu', function(d, i){self.menuContextNodeOpen(this, d, i);});
	
	nodes.append("rect");
	nodes.append("text");

	nodes.each(function(d)
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
				x: 0,
				y: 0,
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
	
	var transform = d3.transform()
		.scale(0);
	
	selection
		.exit()
			.attr("class", "node-deleted")
			.transition()
				.duration(1000)
				.style("opacity", "0")
				.attr("transform", transform)
				.remove();


	self.nodes = self.frame.select(".layer_nodes").selectAll(".node");
	
	// restore coordinates that we saved earlier in this function
	self.nodes.each(function(d)
		{
			var coordinates = self.locals.coordinates.get(this);

			d.x = coordinates.x;
			d.y = coordinates.x;
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
	
	var links = selection
		.enter()
			.append("g")
				.attr("class", "link")
				.attr("data_id", function(d){return d.id;})
	
	links.append("line");

	links.each(function(d)
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
	
	//self.simulationRestart();
};

Tree.prototype.updateDataNLevels = function(node_id, levels_num_children, levels_num_parents, data)
{	

	var self = this;

	console.log("Updating data for N Levels with:", data);

	var ids_updated;

	//Get Sets() of Ids to update the data for
	ids_updated.children = self.getNLevelIds(node_id, levels_num_children);
	ids_updated.parents = self.getNLevelIds(node_id, levels_num_parents);

	
	//combine children and parent sets
	var ids_updated_combined;
	
	ids_updated_combined.nodes = new Set(function*() { yield* ids_updated.children.nodes; yield* ids_updated.parents.nodes; }());
	ids_updated_combined.links = new Set(function*() { yield* ids_updated.children.links; yield* ids_updated.parents.links; }());
	
	//Convert those ID Set()s into D3 selections
	var selection_updated = {};
	
	selection_updated.nodes = filterSelectionsByIDs(self.nodes, ids_updated_combined.nodes, "data_id");
	selection_updated.links = filterSelectionsByIDs(self.links, ids_updated_combined.links, "data_id");
	
	self.updateDataNodes(selection_updated.nodes, data.nodes);
	self.updateDataLinks(selection_updated.links, data.connections);

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

Tree.prototype.drawNodes = function()
{
	var self = this;

	self.nodes.each(function(d)
		{
			//Get the style object for the current node
			var style = self.locals.style.get(this);
			
			// Check if the style has changed since the last time we rendered 
			// so we don't run uncessesary code
			if (style.updated === true)
			{
				var transition = d3.transition();
				transition.duration(500);
				transition.ease(d3.easeBackOut.overshoot(0.8));

				var node = d3.select(this);

				// Only run this code when we are not centering
				// (since we do not need to change these transitions when centering)
				
				node.attr('transform', d3.transform()
					.translate(function(d)
						{
							return [d.x, d.y];
						}
					)
				);

				node.select("rect")
					.attr("fill", function(d)
						{
							return style.color;
						}
					)
					.attr('transform', d3.transform()
						.translate(function(d)
							{
								var width = this.width.animVal.value;
								var height = this.height.animVal.value;
								return [-width/2, -height/2];
							}
						)
					);

				node.select("rect")
					.transition(transition)
						.attr("width", function(d)
							{
								return style.width;
							}
						)
						.attr("height", function(d)
							{
								return style.height;
							}
						)
						.attr("rx", function(d)
							{
								if (style.level === -1)
									return 0;
								else
									return style.width/2;
							}
						);

				if(style.level <= 1) // leaves text hidden for level 2+ nodes
				{
					node.select("text")
						.text(function(d){return d.name;});
				}

				node.select("text")
					.transition(transition)
						.on("start", function(d)
							{	
								if (style.labeled)
								{
									this.style.visibility = "unset";
								}
							}
						)
						.on("end", function(d)
							{
								if (!style.labeled)
								{
									this.style.visibility = "hidden";
								}
							}
						)
						.attr("opacity",function(d)
							{
								return style.opacity;
							}
						);
			}
			
		}
	);
	
};

Tree.prototype.draw = function()
{
	var self = this;
	
	self.drawNodes();
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

Tree.prototype.computeTreeAttributes = function(ID_Level_Map)
{
	var self = this;
	
	//Set the new level of each of the nodes in our tree

	var ancestor_index = 0;

	self.nodes
		.attr("class", "node")
		.each(function(d)
			{
				var style = self.locals.style.get(this);
				var coordinates = self.locals.coordinates.get(this);

				var level = ID_Level_Map.get(d.id);

				//Label the node with the level computed in the ID_level_Map
				style.level = level;
				
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
				return 50;	
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

Tree.prototype.centerOnNode = function (node)
{
	//This function centers the tree visualization on a node.
	
	var self = this;
	
	var root_node_ID = node.__data__.id;
	
	//Get ids for nodes one level up and 2 levels down
	var ID_Level_Map = self.getIDLevelMaps(root_node_ID, 1, 2);
	
	self.computeTreeAttributes(ID_Level_Map);

	self.simulation.stop();
	
	//Set the on click handlers
	self.nodes.on("click", function(d)
	{

		var style = self.locals.style.get(this);

		switch (style.level)
			{
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

	var transition = d3.transition();
				transition.duration(500);
				transition.ease(d3.easeBackOut.overshoot(0.8));

	self.nodes
		.transition(transition)
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
					if (!self.locals.style.get(this).visible)
					{
						this.style.visibility = "hidden";
					}
				}
			)
			.attr("opacity",function(d)
				{
					return self.locals.style.get(this).opacity;
				}
			)
			.tween("coordinates", function(d)
				{
					return self.nodeCoordinateInterpolatorGenerator.bind(self)(d, this);
				}
			);

	self.simulation.stop();
	
	self.nodes_simulated = self.nodes.filter(function(d)
		{
			var style = self.locals.style.get(this);
			return style.level !== undefined;
		}	
	);
	self.links_simulated = self.links.filter(function(d)
		{
			var style = self.locals.style.get(this);
			return style.level !== undefined;
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
	//self.server.getData(data_id, 1, 2, self.updateDataNLevels.bind(self), function (){});
	self.centerOnNode(node);
};


Tree.prototype.menuContextNodeOpen = function(node, data, index)
{	
	var self = this;

	var menu_context_items = 
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
				action: null
			},
		add:
			{
				title: 'Add',
				icon:  'add',
				color: 'green',
				enabled: true,
				action: null
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

	menu_context = d3.selectAll('.menu_context').html('');
	
	var list = menu_context.append('ul');
	
	var menu_items = list.selectAll('li')
			.data(Object.values(menu_context_items))
			.enter()
				.append('li')
				.classed('item', true);

	menu_items
		.style('color', function(d)
			{
				return d.color;
			}
		)
		.attr('enabled', function(d)
			{
				return d.enabled;
			}	
		)
		.on('click', function(d) 
			{	
				d3.event.stopPropagation();

				if (d.enabled)
				{
					menu_context.style('display', 'none');
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
							self.menu_context.style('display', 'none');
							if(d.action !== null)
							{
								d.action.bind(self)(node, data, index);
							}
						}, 
						
						500
					);
					console.log('Clicked context menu item' + d.title)
				}
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
					
	// display context menu
	self.menu_context
		.style('left', (d3.event.pageX - 2) + 'px')
		.style('top', (d3.event.pageY - 2) + 'px')
		.style('display', 'block');

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

	node_captured_data.push(data);

	self.captureBarRender(node_captured_data);
};

Tree.prototype.nodeUncapture = function(node, data, index)
{
	var self = this;

	d3.select(node.parentNode).remove();

	self.nodes_captured = self.frame.svg.selectAll(".node_captured");

	var node_captured_data = self.nodes_captured.data();

	self.captureBarRender(node_captured_data);
};