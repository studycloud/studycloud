<?php

namespace App\Http\Middleware;

use Closure;

class CheckStatus
{
    /**
     * Check whether the item in the route parameter is disabled.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $class_name=null)
    {
        // assume that the first route parameter is the id of the item
        $item = $request->route()->parameters();
        // is there an item to check? if so, retrieve it
        // if not, error out
        if (count($item) >= 1)
        {
            // get the item
            $item = array_values($item)[0];
        }
        elseif ($request->input('id') && class_exists($class_name))
        {
            // if there is an ID associated with the request, we can still attempt to retrieve the item
            $item = (new $class_name)::findOrFail($request->input('id'));
        }
        else
        {
            abort(400, "Could not check status of item.");
        }

        // finally, we can check the status of the item
        if ($item->status != 0)
        {
            return $next($request);
        }
        abort(403, "This item is currently disabled. You can't perform the requested action at this time.");
    }
}
