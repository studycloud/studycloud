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
		$this->validate($res);
		// now check that there wasn't an error
		if ($res->getStatusCode() == 200 || !is_null($res->getBody()['error']))
		{
			abort($res->getStatusCode(), $res->getBody()['error']);
		}
		// convert the class data to a format we can work with
		$classes = $this->toClasses($res->getBody());
		// tell the user if it worked
		return $res->getBody();
	}

	protected function validate($response)
	{
		$validator = Validator::make($res->getBody(), [

		]);
	}

	protected function toClasses($value='')
	{
		# code...
	}

}
