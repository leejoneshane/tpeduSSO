<?php

namespace App\Http\Middleware;

use Closure;
 
 class CachingMiddleware
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
         $response = $next($request);
         $response->header('Cache-Control', 'max-age=31536000, public'); // 瀏覽器快取為一年
         return $response;
     }
 }