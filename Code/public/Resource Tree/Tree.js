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
			.selectAll(".link")
	
	self.nodes = self.frame.svg
		.append("g")
			.attr("class", "layer_nodes")
			.selectAll(".node")
			

	
	self.simulationInitialize();			
	
	self.setData(data);	
}


Tree.prototype.simulationInitialize = function()
{
	var self = this
	
	self.simulation = d3.forceSimulation()
	
	self.simulation
		.alphaTarget(-1)
		.alphaDecay(0.01)
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
			.strength(.6)
			.distance(10);
			
	self.simulation
		.force("ForceCharge")
			.strength(-200);

};

Tree.prototype.simulationRestart = function()
{
	var self = this
	
	self.simulation.nodes(self.nodes.data());
	self.simulation.force("ForceLink").links(self.links.data())
	self.simulation.restart();
	self.simulation.alpha(1);
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
					.attr("r", 10);
		
	selection	
		.exit()
			.select("circle")
				.transition()
					.duration(500)
					.attr("r", "0");
	
	selection
		.exit()
			.attr("class", "node-deleted")
			.transition()
				.duration(500)
				.style("opacity", "0")
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
			.select("line")
				.transition()
					.duration(500)
					.style("stroke", "transparent");
	
	selection	
		.exit()
			.attr("class", "link-deleted")
			.transition()
				.duration(500)
				.style("opacity", "0")
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