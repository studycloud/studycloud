function SelectionSubtract(SelectionA, SelectionB)
{
	//Returns the difference of Selection A and Selection B. This gets all of the nodes in A that aren't in Boolean
	//A-B = Result
	
	var NodesB = SelectionB.nodes();
	
	var SelectionResult = SelectionA.filter(function()
		{
			if (NodesB.includes(this))
			{
				//this element of A is also in B, so return false
				return false;
			}
			else
			{
				return true;
			}
		}
	);
	
	return SelectionResult;
}

function SelectionAdd(SelectionA, SelectionB)
{
	//Returns the sum of Selection A and Selection B. 
	//A+B = Result
	
	var NodesB = SelectionB.nodes();
	var NodesA = SelectionA.nodes();
	
	var nodes_combined = NodesA.concat(NodesB);
	
	return d3.selectAll(nodes_combined);
}

function filterSelectionsByIDs(selection, IDs, field)
{
	var selection_filtered = selection.filter(function ()
	{
		return IDs.has(this.getAttribute(field));
	}
	);

	return selection_filtered;
}