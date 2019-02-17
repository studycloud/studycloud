<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\Resource;

class ResourceResource extends Resource
{
	/**
	 * ROUTES FOR THIS CONTROLLER
	 *	HTTP Verb	URI						Route Name		Action
	 *	GET 		/data/resource?id={id}	resources.json	get the JSON representation of this resource
	 */

	/**
	 * Transform a Resource (and its contents) into an array.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return array
	 */
	public function toArray($request)
	{
		// set meta tag that specifies the resource's meta data
		$meta = [
			"name" => $this->name,
			"author_name" => $this->author->name(),
			"author_type" => $this->author->type,
			"use_name" => $this->use->name
		];

		/**
		 * set contents of this resource to be
		 * a list of collections each with the following attributes:
		 * 	id
		 * 	name
		 * 	type
		 * 	content
		 * 	created - formatted as April 1, 2018 1:05 AM
		 * 	updated - formatted as April 1, 2018 1:05 AM
		 */
		$date_format = 'M j, Y g:i A';
		$contents = $this->contents->map(
			function($content) use ($date_format)
			{
				$new_content = collect($content);
				$new_content['created'] = $content['created_at']->format($date_format);
				$new_content['updated'] = $content['updated_at']->format($date_format);
				return $new_content->only(['id', 'name', 'type', 'content', 'created', 'updated']);
			}
		)->toArray();

		return ["meta" => $meta, "contents" => $contents];
	}
}
