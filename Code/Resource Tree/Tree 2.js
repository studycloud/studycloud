var svg = d3.select("svg"),
    width = +svg.attr("width"),
    height = +svg.attr("height");

d3.json("miserables.json", function(error, graph) {
  if (error) throw error;

var GraphFrameInner = svg.append("g");
 
var link = GraphFrameInner
    .selectAll(".link")
    .data(graph.links)
		.enter()
			.append("line")
				 .attr("class", "link")

  var node = GraphFrameInner
    .selectAll(".node")
    .data(graph.nodes)
    .enter()
		.append("circle")
		.attr('class', 'node')
		.attr("r", 5)

  var simulation = d3.forceSimulation()
    .force("link", d3.forceLink().id(function(d) { return d.id;}))
    .force("charge", d3.forceManyBody())
    .force("center", d3.forceCenter(width / 2, height / 2));
		
  simulation
      .nodes(graph.nodes)
      .on("tick", ticked);

  simulation.force("link")
      .links(graph.links);

  function ticked() {
    link
        .attr("x1", function(d) { return d.source.x; })
        .attr("y1", function(d) { return d.source.y; })
        .attr("x2", function(d) { return d.target.x; })
        .attr("y2", function(d) { return d.target.y; });

    node
        .attr("cx", function(d) { return d.x; })
        .attr("cy", function(d) { return d.y; });
  }
});