// Add / Update a key-value pair in the URL query parameters
function updateUrlParameter(uri, key, value) {
	// encode the value for use in the URL
	value = encodeURIComponent(value);
    // remove the hash part before operating on the uri
    var i = uri.indexOf('#');
    var hash = i === -1 ? ''  : uri.substr(i);
         uri = i === -1 ? uri : uri.substr(0, i);

    var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
    var separator = uri.indexOf('?') !== -1 ? "&" : "?";
    if (uri.match(re)) {
        uri = uri.replace(re, '$1' + key + "=" + value + '$2');
    } else {
        uri = uri + separator + key + "=" + value;
    }
    return uri + hash;  // finally append the hash as well
}

function update_search_results(results) {
	results_html = "";
	if (results.length <= 0)
	{
		results_html += "<div style='padding: 0.7em'>Your search found no results.</div>";
	}
	else
	{
		results.forEach(function(resource){
			results_html += search_result_html(resource);
		});
	}
	$('#search-results').html(results_html);
}

$('#search-form').submit(
	function(event) {
		if ($('#search-results').length > 0)
		{
			// make sure the user sees a loading message, if only for a second
			$('#search-results').html("<div style='padding:0.7em'>Loading search results...</div>");
			// first, retrieve the query that the user typed
			query = $(this).children(['#search-text'])[0].value;
			// now, make an asynchronous call to retrieve the query results from the server
			$.get('/data/search', {'q':query}, function(results){
				update_search_results(results);
				// lastly, update the url with the new results
				history.replaceState(null, '', updateUrlParameter(window.location.href, 'q', query));
			});
			// TODO: handle errors when loading the results
			// return false to prevent the form submission and stop propogation of the event
			return false;
		}
		else
		{
			return true;
		}
	}
);
