<?php
namespace App\Http\Middleware;

use Closure;

class TechnicianMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
 		 $is_technician_logged=$request->session()->get('is_technician_logged');
 		
		 if($is_technician_logged !='yes'){
 			 return redirect('/partner');
		} 
		
		return $next($request);
		
		
    }
}