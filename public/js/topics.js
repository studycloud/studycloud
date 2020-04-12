// server = new Server();
tree_topics = tree;

var data =
{
	"nodes": [
		{
			"name": "Topic Root",
			"author_id": 1,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t0"
		},
		{
			"name": "Topic A",
			"author_id": 1,
			"use_id": 1,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t2"
		},
		{
			"name": "Topic B",
			"author_id": 2,
			"use_id": 3,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t3"
		},
		{
			"name": "Topic C",
			"author_id": 2,
			"use_id": 20,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t11"
		},
		{
			"name": "Topic D",
			"author_id": 3,
			"use_id": 21,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t12"
		},
		{
			"name": "Topic E",
			"author_id": 3,
			"use_id": 22,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t13"
		},
		{
			"name": "Topic AA",
			"author_id": 2,
			"use_id": 3,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t4"
		},
		{
			"name": "Topic BA",
			"author_id": 2,
			"use_id": 1,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t5"
		},
		{
			"name": "Topic AB",
			"author_id": 1,
			"use_id": 3,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t6"
		},
		{
			"name": "Topic AC",
			"author_id": 1,
			"use_id": 3,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t7"
		},
		{
			"name": "Topic BB",
			"author_id": 4,
			"use_id": 3,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t8"
		},
		{
			"name": "Topic BBA",
			"author_id": 4,
			"use_id": 2,
			"created_at": "2017-11-02 21:02:03",
			"updated_at": "2017-11-02 21:02:03",
			"id": "t9"
		}
	],
	"connections": [
		{
			"source": "t0",
			"target": "t2",
			"id": "l1"
		},
		{
			"source": "t0",
			"target": "t11",
			"id": "l10"
		},
		{
			"source": "t0",
			"target": "t12",
			"id": "l11"
		},
		{
			"source": "t0",
			"target": "t13",
			"id": "l12"
		},
		{
			"source": "t0",
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
		},
		{
			"source": "t3",
			"target": "t4",
			"id": "l9"
		}
	]
};

var connections = data.connections;
var IDNodeMap = d3.map(data.nodes, function (d) { return d.id; });

connections.forEach(function (connection)
{
	connection.source = IDNodeMap.get(connection.source);
	connection.target = IDNodeMap.get(connection.target);
}
);

tree_topics.setData(data);


tree_topics.nodeClicked(d3.select(".node").node());

//tree_topics.server.getData(0, 1, 3, tree_topics.updateDataNLevels.bind(tree_topics), function (node, url, error) { console.log(node, url, error); });
//tree_topics.setData(data);

// server_topics = new Server();
