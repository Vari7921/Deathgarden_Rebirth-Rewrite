<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\HttpFoundation\Response;

class AccessLogger
{
    /**
     * Handle an incoming request.
     *
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Str::contains($request->userAgent(), 'TheExit'))
            return $next($request);

        if(config('database.enable-query-logging'))
            DB::enableQueryLog();

        $response = $next($request);

        $log = new stdClass();
        $log->method = $request->method();
        $log->url = $request->fullUrl();
        $log->headers = $request->headers->all();
        $log->queryParams = $request->getQueryString();
        $log->body = $request->all();

        $channels = ['dg_requests'];

        $logChannel = static::getSessionLogConfig();

        $channels[] = $logChannel;

        $log->response = new stdClass();
        $log->response->statusCode = $response->getStatusCode();
        $log->response->body = json_decode($response->getContent());
        $log->queries = DB::getQueryLog();

        $logMessage = $log->method . ' ' . $log->url . "\n" . json_encode($log);

        if ($response->isSuccessful())
            Log::stack($channels)->info($logMessage);
        else
            Log::stack($channels)->warning($logMessage);

        return $response;
    }

    public static function getSessionLogConfig(): LoggerInterface
    {
        $user = Auth::user();
        if (!Session::has('sessionLogConfig')) {
            $username = $user?->last_known_username ?? '';
            static::cleanupUsername($username);
            $logConfig = [
                'driver' => 'single',
                'path' => storage_path('logs/sessions/' . $username . '_' . Str::substr(Session::getId(), 0, 12) . '.log')
            ];
            Session::put('sessionLogConfig', $logConfig);
        } else
            $logConfig = Session::get('sessionLogConfig');

        if($user !== null && str_starts_with(basename($logConfig['path']), '_')) {
            $oldPath = $logConfig['path'];
            $username = $user->last_known_username;
            static::cleanupUsername($username);
            $newPath = storage_path('logs/sessions/'.$username.basename($oldPath));
            rename($oldPath, $newPath);
            $logConfig['path'] = $newPath;
            Session::put('sessionLogConfig', $logConfig);
        }

        return Log::build($logConfig);
    }

    public static function cleanupUsername(string &$username): void
    {
        $username = preg_replace('/[^\w\-\.]/', '', $username);
    }
}
