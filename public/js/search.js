// Add / Update a key-value pair in the URL query parameters
function updateUrlParameter(uri, key, value) {
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
	if (results.length <= 0)
	{
		results = "Your search found no results.";
	}
	$('#search-results').html(results);
}

$(document).ready(function() {
	$('#search-form').submit(
		function(event) {
			if ($('#search-results').length > 0)
			{
				// first, retrieve the query that the user typed
				query = $(this).children(['#search-text'])[0].value;
				// now, make an asynchronous call to retrieve the query results from the server
				$.get('/data/search', {'q':query}, function(results){
					console.log(results);
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
});