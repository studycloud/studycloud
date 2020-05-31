<?php

namespace App\Http\Requests\CourseImportAPIs;

use GuzzleHttp\Client;
use App\Academic_Class;
use App\Http\Requests\CourseImportRequest;
use Illuminate\Support\Facades\Validator;

// CourseImportRequest is an abstract class
class HyperscheduleAPI extends CourseImportRequest
{
	/**
	 * the url from which to get the data
	 */
	// protected $url = "https://hyperschedule.herokuapp.com/api/v3/courses";
	protected $url = "http://localhost:8001/courses.json";

	/**
	 * the Guzzle client
	 */
	protected $client;

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		// check that school is valid according to https://github.com/MuddCreates/hyperschedule-api/blob/03e2d871fab46b8a23a96d4d7eefda07074cf8fb/hyperschedule/app.py#L75
		return [
			'parent_id' => 'string|nullable',
			'school' => 'string|in:cmc,hmc,pitzer,pomona,scripps|required'
		];
	}

	/**
	 * import the data associated with this API and add it to the database
	 */
	public function course_import()
	{
		// first, instantiate the guzzle client to make the request
		$this->client = new Client();

		// also, retrieve the input variables
		$parent_id = $this->input('parent_id');
		$school = $this->input('school');

		// make the request to their API
		$res = $this->client->request('GET', $this->url, [
			'query' => [
				'school' => $school
			]
		]);
		// validate the response! <-- important to make sure the data doesn't have anything sneaky in it!
		$response = $this->validate($res);
		// now check that there wasn't an error
		if ($res->getStatusCode() != 200 || !is_null($response['error']))
		{
			abort($res->getStatusCode(), $response['error']);
		}
		// TODO: check if the since value is stored in the meta table
		// and if it is, do a diff instead
		
		// convert the class data to a format we can work with
		$classes = $this->toClasses($response['data']['courses'], $parent_id);
		return $classes;
		// tell the user if it worked
		return $response;
	}

	protected function validate($res)
	{
		return json_decode($res->getBody()->__toString(), true);
		return Validator::make($response, [
			'error' => 'string|nullable|required',
			'data' => 'exclude_if:error,null|array|required',
			'data.courses' => 'exclude_if:error,null|array|required',
			'data.courses.courseName' => 'exclude_if:error,null|string|required|max:255|required',
			'until' => 'exclude_if:error,null|integer|required',
			'full' => 'exclude_if:error,null|boolean'
		])->validate();
		// TODO: validate the data, too
	}

	protected function toClasses($courses, $parent_id)
	{
		$classes = collect();
		$parent = Academic_Class::find($parent_id);
		foreach ($courses as $course_code => $course)
		{
			$class = new Academic_Class();
			$class->name = $course['courseName'];
			if (!is_null($parent_id))
			{
				$class->parent()->associate($parent);
			}
			// $class->save();
			$classes = $classes->push($class);
		}
		return $classes;
	}

	protected function diffClasses($courses, $parent_id)
	{
		// go through each class and check if we already have it in the database
		// if it doesn't appear in the diff, leave it in the database
		// if it's been deleted in the diff (ie if its value is $delete), then we
		// should attempt to delete it from the database
		// otherwise, if its changed, we should try to change it in the database
	}

}
