<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOrClient
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->guard('admin_details')->check() && auth()->guard('admin_details')->user()->tokenCan('admin') || auth()->guard('client_details')->check()  && auth()->guard('client_details')->user()->tokenCan('client')){
        return $next($request);}
        else{

            return response()->json(['status' => false, 'message' => 'Unauthourized Please Login Or Register'], 500);
      
        }
    }
}
