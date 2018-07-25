<?php

namespace App\Http\Controllers;

use App\Resource;
use Illuminate\Http\Request;

class ResourceDataController extends Controller
{
	/**
	ROUTES FOR THIS CONTROLLER
		GET	/resources/data?id={id}	resources.json	get the JSON representation of this resource
	 */

	/**
	 * the resource upon which to operate
	 * @var Resource
	 */
	protected $resource;

	/**
	 * read query string params and set them up
	 * @param Request $request [description]
	 */
	public function __construct(Request $request)
	{
		$resource_id = $request->query('id');
		if ($resource_id == "")
		{
			$resource_id = null;
		}
		$this->resource = $resource_id;
		if ($this->resource != null)
		{
			// TODO: verify that this doesn't open us up to query injection
			$this->resource = Resource::find($this->resource);
		}
	}

	/**
	 * convert a resource to JSON
	 * @return Collection	the json representation of this resource
	 */
	public function getResource()
	{
		// TODO: handle a null resource. perhaps return all resources?
		
		$json = collect();
		$author = $this->resource->author;
		// set meta tag that specifies the resource's meta data
		$json['meta'] = collect([
							"name" => $this->resource->name,
							"author_name" => $author->name(),
							"author_type" => $author->type
						]);
		/**
		set contents of this resource to be
		a list of collections each with the following attributes:
			name
			type
			content
			created - formatted as April 1, 2018 1:05 AM
			updated - formatted as April 1, 2018 1:05 AM
		**/
		$date_format = 'M j, Y g:i A';
		$json['contents'] = $this->resource->contents->map(
			function($content) use ($date_format)
			{
				$new_content = collect($content);
				$new_content['created'] = $content['created_at']->format($date_format);
				$new_content['updated'] = $content['updated_at']->format($date_format);
				return $new_content->only(['name', 'type', 'content', 'created', 'updated']);
			}
		);
		return $json;
	}
}
