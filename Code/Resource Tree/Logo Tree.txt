NodesNum = 100

var graph = {"nodes": [], "links": [] };

graph["nodes"] = new Array(NodesNum);

for(i=0; i<NodesNum; i++)
{	
	graph["nodes"][i] = {id: i} 
	graph["links"].push({target:Math.floor(Math.random() * NodesNum) , source: i});
}


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
			.attr('cx', function(d) { return d.x; })
		    .attr('cy', function(d) { return d.y; });
	
		link
			.attr('x1', function(d) { return d.source.x })
			.attr('y1', function(d) { return d.source.y  })
			.attr('x2', function(d) { return d.target.x  })
			.attr('y2', function(d) { return d.target.y  });
	}