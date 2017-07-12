
//var graph = {"nodes": [], "links": [] };
var LogoSVG = d3.select("#svg5045");

var LogoData = 
{
	"Stars":
	[
		{"id":"star1"},
		{"id":"star2"},
		{"id":"star3"},
		{"id":"star4"},
		{"id":"star5"},
		{"id":"star6"},
		{"id":"star7"},
		{"id":"star8"},
		{"id":"star9"}
	], 
	"Links":
	[
		{"id":"line1_2", "source":"star1", "target":"star2"},
		{"id":"line1_3", "source":"star1", "target":"star3"},
		{"id":"line2_7", "source":"star2", "target":"star7"},
		{"id":"line2_5", "source":"star2", "target":"star5"},
		{"id":"line2_4", "source":"star2", "target":"star4"},
		{"id":"line3_4", "source":"star3", "target":"star4"},
		{"id":"line3_6", "source":"star3", "target":"star6"},
		{"id":"line3_8", "source":"star3", "target":"star8"},
		{"id":"line4_5", "source":"star4", "target":"star5"},
		{"id":"line4_6", "source":"star4", "target":"star6"},
		{"id":"line5_7", "source":"star5", "target":"star7"},
		{"id":"line5_9", "source":"star5", "target":"star9"},
		{"id":"line6_8", "source":"star6", "target":"star8"},
		{"id":"line6_9", "source":"star6", "target":"star9"},
		{"id":"line7_9", "source":"star7", "target":"star9"},
		{"id":"line8_9", "source":"star8", "target":"star9"}
	],
	"Halos":
	[
		{"id":"halo1", "star":"star1"},
		{"id":"halo2", "star":"star2"},
		{"id":"halo3", "star":"star3"},
		{"id":"halo4", "star":"star4"},
		{"id":"halo5", "star":"star5"},
		{"id":"halo6", "star":"star6"},
		{"id":"halo7", "star":"star7"},
		{"id":"halo8", "star":"star8"},
		{"id":"halo9", "star":"star9"}
	]
	
	
};

var IDStarMap = d3.map(LogoData.Stars, getID);

var Stars = LogoSVG.selectAll("#layer2 > circle");
	
Stars.data(LogoData.Stars, function(d, i, nodes)
	{	
		if(d)
		{
			return d.id;
		}else
		{
			return this.id;
		}
	}
);
 
Stars.each(function(d, i, nodes)
	{
		d.x = this.cx.baseVal.value;
		d.y = this.cy.baseVal.value;
		d.InitialX = d.x;
		d.InitialY = d.y;
		
	}
);
 
//console.log(Stars.data());
 
var Halos = LogoSVG.selectAll("#layer3 > circle");

Halos.data(LogoData.Halos, function(d, i, nodes)
	{	
		if(d)
		{
			return d.id;
		}else
		{
			return this.id;
		}
	}
);

Halos.each(function(d,i,nodes)
	{
		d.star = IDStarMap.get(d.star);
		d.OffsetX = this.transform.baseVal[0].matrix.e - d.star.x;
		d.OffsetY = this.transform.baseVal[0].matrix.f - d.star.y;
		
	}
);

console.log(Halos.data());

var Lines = LogoSVG.selectAll("#layer1 > path");

Lines.data(LogoData.Links, function(d, i, nodes)
	{	
		if(d)
		{
			return d.id;
		}else
		{
			return this.id;
		}
	}
); 

Lines.each(function(d,i,nodes)
	{
		d.InitialLength = this.getTotalLength();
	}
);

//console.log(Lines.data());
//console.log(Lines.nodes());
 
 // Stars.each(function(p, j) {
	// console.log(p);
	// console.log(this)
 // });

 var SimulationForce = d3.forceSimulation()
	.stop()
	.alphaDecay(0)
	.force("ForceLink", d3.forceLink())
	// .force("ForceCharge", d3.forceManyBody())
	.force("HomingX", d3.forceX())
	.force("HomingY", d3.forceY());

 SimulationForce
	 .nodes(LogoData.Stars)
	 .on('tick', GraphUpdate);

 SimulationForce.force("ForceLink")
	 .id(getID)
	 .links(LogoData.Links)
	 .strength(.1)
	 .distance(function(d){return d.InitialLength;});
	 
	 
// SimulationForce.force("ForceCharge")
	// .strength(-10);
	
SimulationForce.force("HomingX")
	.x(function(node, index){return node.InitialX;})
	.strength(.1);
	
SimulationForce.force("HomingY")
	.y(function(node, index){return node.InitialY;})
	.strength(.1);

Stars.call(d3.drag()
          .on("start", DragStart)
          .on("drag", Dragging)
          .on("end", DragEnd));	

function DragStart(d)
{
	d.fx = d.x;
	d.fy = d.y;
}	

function Dragging(d)
{
	d.fx = d3.event.x;
	d.fy = d3.event.y;
}

function DragEnd(d)
{
	d.fx = null;
	d.fy = null;
}
		  
function getID(d) 
{ 
	return d.id; 
}	

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

function GraphUpdate()
	{
		
		Stars
			.attr('cx', function(d) { return d.x; })
		    .attr('cy', function(d) { return d.y; });
	
		Lines
			.attr('d', function(d) { string = "M " + d.source.x + "," + d.source.y + " " + d.target.x + "," + d.target.y; return string })
			
		Halos
			.each(function(d, i, nodes) 
				{ 
					this.transform.baseVal[0].matrix.e = d.star.x + d.OffsetX;
					this.transform.baseVal[0].matrix.f = d.star.y + d.OffsetY;
					
				}
		    );
		
	}


// for(i=0; i<NodesNum; i++)
// {	
	// graph["nodes"].push({id: i});
	// graph["links"].push({target:Math.floor(Math.random() * NodesNum) , source: i});
// }


// var Width = window.innerWidth,
    // Height = window.innerHeight,
	// center = {x: Width/2, y: Height/2},
	// scale = 1;



// var GraphFrameInner = GraphSVG.append("g");

// var link =  GraphFrameInner
	// .selectAll('.link')
    // .data(graph.links)
    // .enter()
		// .append('line')
			// .attr('class', 'link')
			
// var node =  GraphFrameInner
	// .selectAll('.node')
    // .data(graph.nodes)
    // .enter()
		// .append('circle')
		// .attr('class', 'node')
		// .attr('r', function()
			// {
				// return (Width/100) * (Math.random() * 2 +0.5);
			// })
		// .style("fill", "magenta")
		// .on("click", CenterNode);


// We're about to tell the force layout to start its
// calculations. We do, however, want to know when those
// calculations are complete, so before we kick things off
// we'll define a function that we want the layout to call
// once the calculations are done.

// var SimulationForce = d3.forceSimulation()
    // .force("ForceLink", d3.forceLink())
    // .force("ForceCharge", d3.forceManyBody())
    // .force("ForceCenter", d3.forceCenter(Width / 2, Height / 2));

// SimulationForce
	// .nodes(graph.nodes)
	// .on('tick', GraphUpdate);

// SimulationForce.force("ForceLink")
	// .links(graph.links)
	// .strength(.3);
	
// SimulationForce.force("ForceCharge")
	// .strength(-50);
	
// function CenterNode(Node)
	// {
		// var TranslateX
		// var TranslateY
		
		// if (d3.event.ctrlKey) 
			// {
				// scale = 1
				// TranslateX = 0
				// TranslateY = 0
			// }
		// else
			// {
				// scale = scale * 4
				// TranslateX = center.x - Node.x * scale;
				// TranslateY = center.y - Node.y * scale;
			// }
			
		 // GraphFrameInner.transition()
			// .duration(700)
			// .attr("transform", "translate("+ TranslateX + "," + TranslateY  + ")scale(" + scale +")");
	// }

// function GraphUpdate()
	// {
		// node
			// .attr('cx', function(d) { return d.x; })
		    // .attr('cy', function(d) { return d.y; });
	
		// link
			// .attr('x1', function(d) { return d.source.x })
			// .attr('y1', function(d) { return d.source.y  })
			// .attr('x2', function(d) { return d.target.x  })
			// .attr('y2', function(d) { return d.target.y  });
	// }