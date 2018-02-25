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