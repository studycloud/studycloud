function Tree(type, frame_id)
{		
	//Creates a tree visualization with a type of <type> inside the DOM element <frame_id>
	
	//The job of this constructor is to set up a new tree and 
	//	allocate memory for all of the class level variables that this class uses
	
	//self is a special variable that contains a reference to the class instance itself. 
	//	This is passed from function to function to allow everything to access the class's members
	var self = this;
	
	//These sets contain ids of elements to update when we get new data
	self.updated_node_ids = new Set();
	self.updated_link_ids = new Set();
	
	self.debug = true;

	//This contains all of the data associated with links and nodes
	self.data = {};
	self.data.nodes = [];
	self.data.links = [];

	
	if (self.debug)
	{
		self.nodes_count = 10;
		self.nodes = new Array(self.nodes_count);

		for(i = 0; i < self.nodes_count; i++)
		{	
			self.data.nodes[i] = {id: i};
			self.data.links.push({target:Math.floor(Math.random() * self.nodes_count) , source: i, id:i});
			self.data.links.push({target:Math.floor(Math.random() * self.nodes_count) , source: i, id:-i});
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
				
	self.dataUpdate(self, self.data);
	
	self.simulationInitialize(self);
	
}

Tree.prototype.simulationInitialize = function(self)
{
	self.simulation = d3.forceSimulation()
	
	self.simulation
		.alphaTarget(-1)
		.alphaDecay(0)
		.force("ForceLink", d3.forceLink())
		.force("ForceCharge", d3.forceManyBody())
		.force("ForceCenter", d3.forceCenter(self.frame.boundary.width / 2, self.frame.boundary.height / 2));

	self.simulation
		.nodes(self.data.nodes)
		.on('tick', function(){self.draw(self)});

	self.simulation
		.force("ForceLink")
			.links(self.data.links)
			.id(function(d){return d.id;})
			.strength(.3);
			
	self.simulation
		.force("ForceCharge")
			.strength(-10);

};

Tree.prototype.simulationRestart = function(self)
{
	self.simulation.nodes(self.data.nodes);
	self.simulation.force("ForceLink").links(self.data.links)
	self.simulation.restart();
};

Tree.prototype.dataAdd = function(self, data) 
{
	
	self.data.nodes = self.data.nodes.concat(data.nodes);
	self.data.links = self.data.links.concat(data.links);
	
	self.dataUpdate(self, self.data);
	self.simulationRestart(self);

};


//This function is a recursive function that loops through all links, 
//and gets the children the node with id:node_id
Tree.prototype.getNChildrenRecurse = function(self, node_id, children_levels_num)
{	
	//return early, because we have already seen this node before
	if (self.updated_node_ids.has(node_id))
	{
		return;
	}
	
	
	self.updated_node_ids.add(node_id);
	
	if (children_levels_num = 0)
	{
		//there are no more children to find
		return;
	}
	
	
	self.data.links.forEach(function(link)
		{
			if (link.source.id == node_id)
			{	
				//we found a child, add the id of the link and recurse
				self.updated_link_ids.add(link.id);
				
				self.getNChildren(self, link.target.id, children_levels_num-1);
			}
		}
	);
};

Tree.prototype.getNChildren = function(self, node_id, children_levels_num)
{
	self.updated_node_ids.clear();
	self.updated_link_ids.clear();
	
	getNChildrenRecurse(self, node_id, children_levels_num);
	
	
	
};

Tree.prototype.dataUpdate = function(self, data)
{	
	//debugging stuffs
	//self.getNChildren(self, 1, 2);
	
	//console.log(self.updated_node_ids);
	
	/*TODO: Implement this for real
	
		When we get data back from the server for children of a Node, 
		we need to do so voodoo in order for everything to merge and update correctly
		
		request is for n children of given node including root
		
		1: Find all of the connections that have "source" as the ID of the Node that we're finding children of
		2: For each of those connections, find the ids of the "target" nodes 
			a: Repeat step 1 for each of those target nodes
			b: Add the target nodes ids to a d3.set of found nodes
			c: Add the connection id to a d3 set of found connections
		3: Create a new selection of nodes that is a filter of all nodes to only those in the found nodes set
		4: Create a new selection of connections that is a filter of all connections to only those in the found connections set
		5: Set the data of the new nodes selection to the newly recieved node data
			a: create new DOM visual elements for each of the new nodes
			b: cleanly remove DOM visual elements for nonexistant nodes
		6: Set the data of the new connections selection to the newly recieved connections data
			a: create new DOM visual elements for each of the new connections
			b: cleanly remove DOM visual elements for nonexistant connections
	*/

	var nodes = self.nodes.data(data.nodes, function(d){return d ? d.id : this.data_id; });

	console.log(nodes);
	
	nodes
		.enter()
			.append("g")
				.attr("class", "node")
				.attr("data_id", function(d){return d.id;})
				.append("circle")
					//.attr("cx", function(){return Math.random() * self.frame.boundary.width})
					//.attr("cy", function(){return Math.random() * self.frame.boundary.height})
					.attr("fill", "blue")
					.attr("r", 15);
		
	nodes
		.exit()
			.transition()
				.duration(300)
				.style("display", "none")
				.remove();
	
	self.nodes = self.frame.select(".layer_nodes").selectAll(".node");
				
	var links = self.links.data(data.links, function(d){return d ? d.id : this.data_id; });
	
	links
		.enter()
			.append("g")
				.attr("class", "link")
				.attr("data_id", function(d){return d.id;})
				.append('line');
								
	links	
		.exit()
			.transition()
				.duration(300)
				.style("display", "none")
				.remove();
				
	self.links = self.frame.select(".layer_links").selectAll(".link");
	
};

Tree.prototype.draw = function(self)
{
	//console.log("tick");
		
	self.nodes.selectAll("circle")
		.attr('cx', function(d) { return d.x; })
		.attr('cy', function(d) { return d.y; });
	
	self.links.selectAll("line")
		.attr('x1', function(d) { return d.source.x })
		.attr('y1', function(d) { return d.source.y  })
		.attr('x2', function(d) { return d.target.x  })
		.attr('y2', function(d) { return d.target.y  });	
};



tree_1 = new Tree("Blah", "tree");

/*


var Width = window.innerWidth,
	Height = window.innerHeight,
	center = {x: Width/2, y: Height/2},
	scale = 1;

var GraphSVG = d3.select('body').append('svg')
	.attr('width', Width)
	.attr('height', Height);

var GraphFrameInner = GraphSVG.append("g");

var link =  GraphFrameInner
	.selectAll('.link')
	.data(graph.links)
	.enter()
		.append('line')
			.attr('class', 'link')
			
var node =  GraphFrameInner
	.selectAll('.node')
	.data(graph.nodes)
	.enter()
		.append('circle')
		.attr('class', 'node')
		.attr('r', function()
			{
				return (Width/100) * (Math.random() * 2 +0.5);
			})
		.style("fill", "magenta")
		.on("click", CenterNode);


// We're about to tell the force layout to start its
// calculations. We do, however, want to know when those
// calculations are complete, so before we kick things off
// we'll define a function that we want the layout to call
// once the calculations are done.

var SimulationForce = d3.forceSimulation()
	.force("ForceLink", d3.forceLink())
	.force("ForceCharge", d3.forceManyBody())
	.force("ForceCenter", d3.forceCenter(Width / 2, Height / 2));

SimulationForce
	.nodes(graph.nodes)
	.on('tick', GraphUpdate);

SimulationForce.force("ForceLink")
	.links(graph.links)
	.strength(.3);
	
SimulationForce.force("ForceCharge")
	.strength(-50);

	
function Ticker()
{
	SimulationForce.tick();
	GraphUpdate();
}	

function Starter()
{
	SimulationForce.restart();

}	

function Stopper()
{
	SimulationForce.stop();
}	

function CenterNode(Node)
	{
		var TranslateX;
		var TranslateY;
		var scale

		if (d3.event.ctrlKey) 
			{
				scale = 1;
				TranslateX = 0;
				TranslateY = 0;
			}
		else
			{
				scale = scale * 4;
				TranslateX = center.x - Node.x * scale;
				TranslateY = center.y - Node.y * scale;
			}
			
		 GraphFrameInner.transition()
			.duration(700)
			.attr("transform", "translate("+ TranslateX + "," + TranslateY  + ")scale(" + scale +")");
	}

function GraphUpdate()
	{
		node
			.attr('cx', function(d) { return d.x; })
		    .attr('cy', function(d) { return d.y; });
	
		link
			.attr('x1', function(d) { return d.source.x })
			.attr('y1', function(d) { return d.source.y  })
			.attr('x2', function(d) { return d.target.x  })
			.attr('y2', function(d) { return d.target.y  });
	}
*/
