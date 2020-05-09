<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// an abstract class extending a concrete class!
// when we write code to call other APIs, we can have them just extend CourseImportRequest
abstract class CourseImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        // TODO: check if the current user has the course_import ability
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    abstract public function rules();

    /**
     * import the data associated with this API in the database
     */
    abstract public function course_import();
}
