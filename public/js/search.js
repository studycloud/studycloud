$(document).ready(function() {
	function update_search_results(results) {
		$('#main').html("<div id='main'>"+results+"</div>");
	}

	$('#search-form').submit(
		function(event) {
			// first, retrieve the query that the user typed
			query = $(this).children(['#search-text'])[0].value;
			// now, make an asynchronous call to retrieve the query results from the server
			$.get('/data/search', {'q':query}, function(results){
				console.log(results);
				update_search_results(results);
			});
			// return false to prevent the form submission and stop propogation of the event
			return false;
		}
	);
});