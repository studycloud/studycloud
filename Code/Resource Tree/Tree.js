NodesNum = 100

var graph = {"nodes": [], "links": [] };

graph["nodes"] = new Array(NodesNum);

for(i=0; i<NodesNum; i++)
{	
	graph["nodes"][i] = {} 
	graph["links"].push({target:Math.floor(Math.random() * NodesNum) , source: i});
}


// Define the dimensions of the visualization.

var width = window.innerWidth,
    height = window.innerHeight,
	center = {x: width/2, y: height/2},
	scale = 1;



var nodes = graph.nodes,
    links = graph.links;

var GraphSVG = d3.select('body').append('svg')
	.attr('width', width)
	.attr('height', height);
	
var GraphInnerFrame = GraphSVG.append("g");

var GraphLayout = d3.layout.force()
    .size([width, height])
    .nodes(nodes)
    .links(links);

GraphLayout.gravity(1);
//force.linkDistance(width/3);
//force.linkStrength(0.1);
GraphLayout.charge(-1200);

var link = GraphInnerFrame.selectAll('.link')
    .data(links)
    .enter().append('line')
    .attr('class', 'link');

var node = GraphInnerFrame.selectAll('.node')
    .data(nodes)
    .enter().append('circle')
    .attr('class', 'node')
    .attr('r', function()
		{
			return (width/100) * (Math.random() * 2 +0.5);
		})
	.style("fill", "magenta")
	.on("click", CenterNode);


// We're about to tell the force layout to start its
// calculations. We do, however, want to know when those
// calculations are complete, so before we kick things off
// we'll define a function that we want the layout to call
// once the calculations are done.

GraphLayout.on('tick', GraphUpdate);

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
			
		GraphInnerFrame.transition()
			.duration(700)
			.attr("transform", "translate("+ TranslateX + "," + TranslateY  + ")scale(" + scale +")");
	}

function GraphUpdate()
	{
		node.attr('cx', function(d) { return d.x; })
		    .attr('cy', function(d) { return d.y; });
	
		link.attr('x1', function(d) { return d.source.x; })
			.attr('y1', function(d) { return d.source.y; })
			.attr('x2', function(d) { return d.target.x; })
			.attr('y2', function(d) { return d.target.y; });
	}


// Okay, everything is set up now so it's time to turn
// things over to the force layout. Here we go.

GraphLayout.start();



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

