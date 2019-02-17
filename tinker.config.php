<?php 

/*
|------------------------------------------------------------------
| Application HTTP requests helper
|------------------------------------------------------------------
*/
// Why extending TestCase here? Just because it's sooo easy and consistent across all L versions ;)
class _LocalRequest extends Tests\TestCase
{
    function __construct()
    {
        $this->setUp();
        $this->withoutMiddleware(App\Http\Middleware\VerifyCsrfToken::class);
    }
    function response()
    {
        return $this->response;
    }
    function __call($method, $params)
    {
        return call_user_func_array([$this, $method], $params);
    }
}
/*
|------------------------------------------------------------------
| Helper functions for common tasks
|------------------------------------------------------------------
*/
if (!function_exists('local'))
{
    // create a local() function for making quick HTTP requests to the app
    function local($uri = null)
    {
        return $uri ? (new _LocalRequest)->get($uri) : new _LocalRequest;
    }
}