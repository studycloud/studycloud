NodesNum = 100

var graph = {"nodes": [], "links": [] };

graph["nodes"] = new Array(NodesNum);

for(i=0; i<NodesNum; i++)
{	
	graph["nodes"][i] = {} 
	graph["links"].push({target:Math.floor(Math.random() * NodesNum) , source: i});
}

// Define the dimensions of the visualization.

var Width = window.innerWidth,
    Height = window.innerHeight,
	center = {x: Width/2, y: Height/2},
	scale = 1;

var GraphSVG = d3.select('body').append('svg')
	.attr('width', Width)
	.attr('height', Height);

var GraphFrameInner = GraphSVG.append("g");

var link =  GraphFrameInner.selectAll('.link')
    .data(graph.links)
    .enter()
		.append('line')
			.attr('class', 'link')
			.attr('x1', function(d) { return d.source})
			.attr('y1', function(d) { return d.source})
			.attr('x2', function(d) { return d.target})
			.attr('y2', function(d) { return d.target});
			
var node =  GraphFrameInner.selectAll('.node')
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
	.links();
	
function CenterNode(Node)
	{
		var TranslateX
		var TranslateY
		
		if (d3.event.ctrlKey) 
			{
				scale = 1
				TranslateX = 0
				TranslateY = 0
			}
		else
			{
				scale = scale * 4
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
			.attr('cx', function(d) {console.log(d); return d.x; })
		    .attr('cy', function(d) { return d.y; });
	
		link
			.attr('x1', function(d) { return (graph.nodes[d.source]).x })
			.attr('y1', function(d) { return (graph.nodes[d.source]).y  })
			.attr('x2', function(d) { return (graph.nodes[d.target]).x  })
			.attr('y2', function(d) { return (graph.nodes[d.target]).y  });
	}

	
	
// Okay, everything is set up now so it's time to turn
// things over to the force layout. Here we go.


// By the time you've read this far in the code, the force
// layout has undoubtedly finished its work. Unless something
// went horribly wrong, you should see two light grey circles
// connected by a single dark grey line. If you have a screen
// ruler (such as [xScope](http://xscopeapp.com) handy, measure
// the distance between the centers of the two circles. It
// should be somewhere close to the `linkDistance` parameter we
// set way up in the beginning (480 pixels). That, in the most
// basic of all nutshells, is what a force layout does. We
// tell it how far apart we want connected nodes to be, and
// the layout keeps moving the nodes around until they get
// reasonably close to that value.

// Of course, there's quite a bit more than that going on
// under the hood. We'll take a closer look starting with
// the next example.

