<?php

namespace Common\Domains;

use Closure;
use Illuminate\Http\Request;

class CustomDomainsEnabled
{
    public function handle(Request $request, Closure $next)
    {
         {
            abort(404);
        }

        return $next($request);
    }
}
