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
		results_html += search_message_html("Your search found no results.", true);
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
			// note that this also appears in the blade when the page is first loaded
			$('#search-results').html(search_message_html("Loading search results..."));
			// first, retrieve the query that the user typed
			query = $(this).children(['#search-text'])[0].value;
			// now, make an asynchronous call to retrieve the query results from the server
			$.ajax('/data/search', {
				'data': {
					'q': query
				},
				"timeout": 10000,
				"success": function(results){
					update_search_results(results);
					// lastly, update the url with the new results
					history.replaceState(null, '', updateUrlParameter(window.location.href, 'q', query));
				}, "error": function(error, error_type) {
					recommend = false;
					if (error_type == "error")
					{
						console.log(error);
						if (error.status == 500)
						{
							// try {
							// 	// we have an elasticsearch error
							// 	error = JSON.parse(error.responseJSON.message);
							// 	if (error.status == 400)
							// 	{
							// 		message = "";
							// 		error.error.root_cause.forEach(function(cause)
							// 		{
							// 			message += `Search Error: ${cause.reason}<br>`;
							// 		});
							// 	}
							// 	else
							// 	{
							// 		message = `Search Error: ${error.reason}`;
							// 	}
							// }
							// catch
							// {
							// 	// we have some other weird error
							// 	message = `Search Error: ${error.responseJSON.message}`;
							// }
							message = "There was an error when executing your query.";
							recommend = true;
						}
						else
						{
							message = `Error: ${error.message}`;
						}
					}
					else if (error_type == "timeout")
					{
						message = "Request timed out. Check your internet connection.";
					}
					else
					{
						message = "Unhandled error. Check your query."
						recommend = true;
					}
					$('#search-results').html(search_message_html(message, recommend));
				}
			});
			// return false to prevent the form submission and stop propogation of the event
			return false;
		}
		else
		{
			return true;
		}
	}
);
