<?php

namespace App\TreeAPIs;

use GuzzleHttp\Client;
use App\TreeAPIs\TreeAPI;

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
		$res = $this->client->request('GET', $this->url, [
			'query' => [
				'school' => 'hmc'
			]
		]);
		return $res;
		return $res->getBody();
	}
}
