<?php
namespace App\Http\Middleware;

use Closure;

class AdminMiddleware
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
        
 		 $is_admin_logged=$request->session()->get('is_admin_logged');
 		
		 if($is_admin_logged !='yes'){
 			 return redirect('/admin');
		} 
		
		return $next($request);
		
		
    }
}