function Tree(type, frame_id)
{
	//Creates a tree visualization with a type of <type> inside the DOM element <frame_id>
	var self = this;
	
	
	self.debug = true;

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
	
	// this.frame.svg.append("circle")
	// .attr("fill", "red")
	// .attr("r", this.frame.boundary.height/2)
	// .attr("cx", this.frame.svg.center.x)
	// .attr("cy", this.frame.svg.center.y);
	
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

}

Tree.prototype.simulationRestart = function(self)
{
	self.simulation.nodes(self.data.nodes);
	self.simulation.force("ForceLink").links(self.data.links)
	self.simulation.restart();
}

Tree.prototype.dataAdd = function(self, data) 
{
	self.data.nodes = self.data.nodes.concat(data.nodes);
	self.data.links = self.data.links.concat(data.links);
	
	self.dataUpdate(self, self.data);
	self.simulationRestart(self);
	
};

Tree.prototype.dataUpdate = function(self, data)
{	
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
	
}

Tree.prototype.draw = function(self)
{
	console.log("tick");
		
	self.nodes.selectAll("circle")
		.attr('cx', function(d) { return d.x; })
		.attr('cy', function(d) { return d.y; });
	
	self.links.selectAll("line")
		.attr('x1', function(d) { return d.source.x })
		.attr('y1', function(d) { return d.source.y  })
		.attr('x2', function(d) { return d.target.x  })
		.attr('y2', function(d) { return d.target.y  });
}



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