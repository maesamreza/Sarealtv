<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {     if(auth()->guard('admin_details')->check() && auth()->guard('admin_details')->user()->tokenCan('admin')){
        return $next($request);}
        else{

            return response()->json(['status' => false, 'message' => 'Unauthourized Please Login Or Register As Admin'], 500);
      
        }
    }
}
