<?php

namespace App\TreeAPIs;

use GuzzleHttp\Client;
use App\TreeAPIs\TreeAPI;
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
	public function update()
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
		$classes = $this->toClasses($response);
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

	protected function toClasses($value='')
	{
		# code...
	}

}
