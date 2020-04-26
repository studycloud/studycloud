<?php

namespace App\TreeAPIs;

use GuzzleHttp\Client;
use App\TreeAPIs\TreeAPI;
use App\Academic_Class;
use Illuminate\Support\Facades\Validator;

class HyperscheduleAPI implements TreeAPI
{
	/**
	 * the url from which to get the data
	 */
	protected $url = "https://hyperschedule.herokuapp.com/api/v3/courses";

	/**
	 * the Guzzle client
	 */
	protected $client;


	function __construct()
	{
		$this->client = new Client();
	}

	/**
	 * update the data associated with this API in the database
	 */
	public function update(int $parent_id = null)
	{
		// make the request to their API
		$res = $this->client->request('GET', $this->url, [
			'query' => [
				'school' => 'hmc'
			]
		]);
		// validate the response! <-- important to make sure the data doesn't have anything sneaky in it!
		$response = $this->validate($res);
		// now check that there wasn't an error
		if ($res->getStatusCode() != 200 || !is_null($response['error']))
		{
			abort($res->getStatusCode(), $response['error']);
		}
		// convert the class data to a format we can work with
		$classes = $this->toClasses($response['data']['courses'], $parent_id);
		// tell the user if it worked
		return $response;
	}

	protected function validate($res)
	{
		$response = json_decode($res->getBody()->__toString(), true);
		return Validator::make($response, [
			'error' => 'string|nullable',
			'data' => 'exclude_if:error,null|array',
			'until' => 'exclude_if:error,null|integer',
			'full' => 'exclude_if:error,null|boolean'
		])->validate();
		// TODO: validate the data, too
	}

	protected function toClasses($courses, $parent_id)
	{
		$classes = collect();
		$parent = App\Academic_Class::find($parent_id);
		foreach ($courses as $course_code => $course)
		{
			$class = new Academic_Class();
			$class->name = $course['courseName'];
			if (!is_null($parent_id))
			{
				$class->parent()->associate($parent);
			}
			$classes = $classes->push($class);
		}
		return $classes;
	}

}
