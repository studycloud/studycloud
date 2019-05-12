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
			data.nodes[i] = {id: i.toString()};
			data.connections.push({target:Math.floor(Math.random() * nodes_count) , source: i, id:i.toString()});
			data.connections.push({target:Math.floor(Math.random() * nodes_count) , source: i, id:(i+nodes_count).toString()});
		}

	}
	
	//Create the various DOM element groups needed by the tree
	self.frame = d3.select("#" + frame_id);
	self.frame.boundary = self.frame.node().getBoundingClientRect();
	
	self.frame.on("resize", self.resizeFrame);
	
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
	
	self.menu_context_items = 
	[
		{
			title: 'Delete',
			icon:  'delete',
			color: 'red',
			enabled: true,
			action: function(node, d, i) 
			{
				console.log('Item #1 clicked!');
			}
		},
		{
			title: 'Edit',
			icon:  'edit',
			color: 'purple',
			enabled: true,
			action: function(node, d, i) 
			{
				console.log('Item #1 clicked!');
			}
		},
		{
			title: 'Add',
			icon:  'add',
			color: 'green',
			enabled: true,
			action: function(node, d, i) 
			{
				console.log('Item #1 clicked!');
			}
		},
		{
			title: 'Capture',
			icon:   'playlist_add',
			color: 'blue',
			enabled: true,
			action: function(node, d, i) 
			{
				console.log('Item #1 clicked!');
			}
		},
		{
			title: 'Move',
			icon:   'open_with',
			color: 'blue',
			enabled: true,
			action: function(node, d, i) 
			{
				console.log('Item #1 clicked!');
			}
		},
		{
			title: 'Attach',
			icon:   'link',
			color: 'orange',
			enabled: true,
			action: function(node, d, i) 
			{
				console.log('Item #1 clicked!');
			}
		},
		{
			title: 'Detach',
			icon:   'link_off',
			color: 'red',
			enabled: true,
			action: function(node, d, i) 
			{
				console.log('Item #1 clicked!');
			}
		}
	];
	
	self.frame.on('click.menu_context', function(){self.menu_context.style('display', 'none');});
	
	self.nodes_simulated = {};
	self.links_simulated = {};
	
	self.simulationInitialize();

	var timeout_resize;
	d3.select(window).on("resize", function() 
		{ 
			clearTimeout(timeout_resize);
			timeout_resize = setTimeout(function(){self.simulationRecenter();}, 100); 
		}
	);
	
	
	//if (self.debug) self.setData(data);
	
	self.breadcrumbStack = [0];

	self.server = server;

	//Setup Local variables that we keep track of about nodes in our tree. This is necessary so that they stay persistent across data updates
	self.locals = {};
	self.locals.nodes = {};
	self.locals.links = {};

	
	self.locals.style = d3.local();
	self.locals.coordinates = d3.local();

}

Tree.prototype.simulationInitialize = function()
{
	var self = this;
	
	self.simulation = d3.forceSimulation();
	
	self.simulation
		.alpha(0.5)
		.alphaTarget(-1)
		.alphaDecay(0.002)
		.force("ForceLink", d3.forceLink())
		.force("ForceCharge", d3.forceManyBody())
		.force("ForceCenterX", d3.forceX(self.frame.boundary.width/2))
		.force("ForceCenterY", d3.forceY(self.frame.boundary.height/2));

	self.simulation
		.nodes(self.nodes.data())
		.on('tick', function(){self.draw()});

	self.simulation.force("ForceCenterX")
		.strength(0.11);
	
	self.simulation.force("ForceCenterY")
		.strength(0.11);	
		
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

Tree.prototype.simulationRecenter = function(node)
{
	var self = this;

	self.frame.boundary = self.frame.node().getBoundingClientRect();

	//self.simulation
		//.force("ForceCenterX", d3.forceX(self.frame.boundary.width / 2))
		//.force("ForceCenterY", d3.forceY(self.frame.boundary.height / 2));

	//self.simulationReheat();

	var node_center = self.nodes.filter(function(d){ return d.level === 0; });

	self.centerOnNode(node_center.node());
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
				.on('contextmenu', function(d, i){self.nodeMenuOpen(this, d, i);});
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

				node.select("text")
					.attr("text-anchor", "middle")
					.attr("fill", "white")
					.attr("stroke", "black")
					.attr("stroke-width", "0.02em")
					.attr("font-size", "22")
					.attr("font-family", "sans-serif")
					.attr("font-weight", "bold")
					.text(function(d){return d.name;});

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
				


				// Only run this code if centerOnNode is called
				// (otherwise coordinates will not be calculated for nodeCoordinateInterpolatorGenerator)
				// if (center === true) {
				// 	node
				// 		.transition(transition)
				// 			.on("start", function(d)
				// 				{	
				// 					if (self.locals.style.get(this).visible)
				// 					{
				// 						this.style.visibility = "unset";
				// 					}
				// 				}
				// 			)
				// 			.on("end", function(d)
				// 				{
				// 					if (!self.locals.style.get(this).visible)
				// 					{
				// 						this.style.visibility = "hidden";
				// 					}
				// 				}
				// 			)
				// 			.attr("opacity",function(d)
				// 				{
				// 					return self.locals.style.get(this).opacity;
				// 				}
				// 			)
				// 			.tween("coordinates", function(d)
				// 				{
				// 					return self.nodeCoordinateInterpolatorGenerator.bind(self)(d, this);
				// 				}
				// 			);
				// }

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
	
	switch(self.locals.nodes.level.get(this))
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

Tree.prototype.computeTreeAttributes = function(selections)
{
	var self = this;
	var node_locals = self.locals.nodes;

	//Set the new level of each of the nodes in our tree
	self.nodes
		.attr("class", "node")
		.each(function(d)
			{
				var style = self.locals.style.get(this);
				var coordinates = self.locals.coordinates.get(this);

				style.level = 3;
				style.visible = false;
				style.opacity = 0;
				style.labeled = false;
				style.width = 10;
				style.height = 10;

				coordinates.x =  0;
				coordinates.y =  0;
				coordinates.fx = null;
				coordinates.fy = null;
				coordinates.x_old = d.x;
				coordinates.y_old = d.y;
				coordinates.x_new = null;
				coordinates.y_new = null;
			}
		);
	
	self.links.each(function(d){d.level = 3;});
	
	selections.parents.nodes
		.classed("node-parent", true)
		.each(function(d,i,nodes)
			{
				
				var style = self.locals.style.get(this);
				var coordinates = self.locals.coordinates.get(this);

				style.level = -1;
				style.visible = true;
				style.opacity = 1;
				style.labeled = true;
				style.width = self.frame.boundary.width / nodes.length;
				style.height = 40;

				coordinates.x_new = style.width*i + style.width/2;
				coordinates.y_new = style.height/2;
			}
		);
	selections.parents.links.each(function(d){d.level = -1;});

	selections.root.nodes
		.classed("node-root", true)
		.each(function(d)
			{
				var style = self.locals.style.get(this);
				var coordinates = self.locals.coordinates.get(this);

				style.level = 0;
				style.visible = true;
				style.opacity = 1;
				style.labeled = true;
				style.width = 200;
				style.height = style.width;

				coordinates.x_new = self.frame.boundary.width/2;
				coordinates.y_new = self.frame.boundary.height/2;
			}
		);
	
	selections.children.nodes
		.classed("node-child", true)
		.each(function(d, i, nodes)
			{
				var style = self.locals.style.get(this);
				var coordinates = self.locals.coordinates.get(this);

				style.level = 1;
				style.visible = true;
				style.opacity = 1;
				style.labeled = true;
				style.width = 140;
				style.height = style.width;

				coordinates.x_new = null;
				coordinates.y_new = null;
			}
		);
	selections.children.links.each(function(d){d.level = 1;});

	selections.grandchildren.nodes
		.classed("node-grandchild", true)
		.each(function(d)
			{
				var style = self.locals.style.get(this);
				var coordinates = self.locals.coordinates.get(this);

				style.level = 2;
				style.visible = true;
				style.opacity = 0.3;
				style.labeled = false;
				style.width = 20;
				style.height = style.width;

				coordinates.x_new = null;
				coordinates.y_new = null;		
			}
		);
	selections.grandchildren.links.each(function (d) { d.level = 2; });
	
	self.simulation.force("ForceLink").distance(function(d)
		{
			switch(d.level)
			{
			case -1:
				return 150;
			case 1: 
				return 200;
			case 2: 
				return 50;	
			default:
				return 280;
			}
		}
	);
	
};

Tree.prototype.centerOnNode = function (node)
{
	//This function centers the tree visualization on a node.
	
	var self = this;
	
	data_id = node.__data__.id;

	var ids_ancestor = {}, ids_descendents = {}, ids_combined = {};
	
	//Get ids for nodes one level up and 2 levels down
	ids_ancestor = self.getNLevelIds(data_id, -1);
	ids_descendents = self.getNLevelIds(data_id, 2);
	
	ids_combined.nodes = new Set(function*() { yield* ids_ancestor.nodes; yield* ids_descendents.nodes; }());
	ids_combined.links = new Set(function*() { yield* ids_ancestor.links; yield* ids_descendents.links; }());
	
	var selections = 
	{
		parents: {},
		root: {},
		children:{},
		grandchildren:{}
	};
	
	selections.nodes = filterSelectionsByIDs(self.nodes, ids_combined.nodes, "data_id");
	selections.links = filterSelectionsByIDs(self.links, ids_combined.links, "data_id");
	
	
	selections.parents.nodes = filterSelectionsByIDs(self.nodes, ids_ancestor.nodes_separate[1], "data_id");
	selections.parents.links = filterSelectionsByIDs(self.links, ids_ancestor.links_separate[1], "data_id");
	
	selections.root.nodes = filterSelectionsByIDs(self.nodes, ids_ancestor.nodes_separate[0], "data_id");
	
	selections.children.nodes = filterSelectionsByIDs(self.nodes, ids_descendents.nodes_separate[1], "data_id");
	selections.children.links = filterSelectionsByIDs(self.links, ids_descendents.links_separate[1], "data_id");
	
	selections.grandchildren.nodes = filterSelectionsByIDs(self.nodes, ids_descendents.nodes_separate[2], "data_id");
	selections.grandchildren.links = filterSelectionsByIDs(self.links, ids_descendents.links_separate[2], "data_id");
	
	
	self.computeTreeAttributes(selections);

	//Set the on click handlers
	self.nodes.on("click", function(d)
	{
		switch (self.locals.nodes.level.get(this))
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

	self.simulation.stop();
	
	//Set the on click handlers
	self.nodes.on("click", function(d)
	{
		switch (self.locals.style.get(this).level)
			{
				case -1:
				case 1:
				case 2:
					self.nodeClicked(this);
					break;
				default:
					//self.nodeClicked(this)
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
	
	self.nodes_simulated = selections.nodes;
	self.links_simulated = selections.links;

	
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

Tree.prototype.nodeMenuOpen = function(node, data, index)
{
	
	
	var self = this;

	d3.selectAll('.menu_context').html('');
	
	var list = self.menu_context.append('ul');
	
	var menu_items_new = list.selectAll('li')
			.data(self.menu_context_items)
			.enter()
				.append('li')
	
	menu_items_new
		.style('color', function(d)
			{
				return d.color;
			}
		)
		.style('display', function(d)
			{
				return d.enabled ? "default" : "none";
			}	
		)
		.on('click', function(d) 
			{	
				d.action(node, data, index);
				self.menu_context.style('display', 'none');
			}
		)
		.on('touchstart', function(d) 
			{	
				setTimeout(function(){self.menu_context.style('display', 'none');}, 500);
				d.action(node, data, index);
				d3.event.preventDefault();
				//alert("touchdown");
			}
		);
	
	menu_items_new
		.append('i')
			.classed('material-icons', true)
			.text(function(d) 
				{
					return d.icon;
				}
			);
	menu_items_new
		.append('span')
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
}