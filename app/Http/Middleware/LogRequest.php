<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LogRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        $requestId = uniqid('req_');
        
        $logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/requests.log'),
        ]);

        $logger->info('Incoming Request:', [
            'req_id'  => $requestId,
            'url'     => $request->fullUrl(),
            'method'  => $request->method(),
            'ip'      => $request->ip(),
            'user_id' => Auth::check() ? Auth::id() : 'Guest',
            // 'params'  => $request->except(['password', 'password_confirmation', 'credit_card']),
        ]);

        try {
            $response = $next($request);
            return $response;
        } finally {
            $duration = number_format(microtime(true) - $startTime, 3);
            
            $logger->info('Outgoing Response:', [
                'req_id'   => $requestId,
                'status'   => isset($response) ? $response->getStatusCode() : 500,
                'duration' => $duration . ' seconds',
            ]);
        }
    }
}
