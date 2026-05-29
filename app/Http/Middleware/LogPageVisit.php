<?php

namespace App\Http\Middleware;

use App\Models\StaffUser;
use App\Models\UserPageLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogPageVisit
{
    // Routes never worth logging (auth, exports, health, log-viewer API, etc.)
    private const SKIP = [
        'login', 'login.post', 'logout',
        'settings.pageLog*',
        'log-viewer.*',
        'livewire.*', 'telescope.*', 'horizon.*',
        'debugbar.*',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $staffUserId = $request->session()->get('staff_user_id');
        if (!$staffUserId) {
            return;
        }

        $routeName = $request->route()?->getName();
        if (!$routeName) {
            return;
        }

        foreach (self::SKIP as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return;
            }
        }

        // Skip CSV / file downloads
        $ct = $response->headers->get('Content-Type', '');
        if (str_contains($ct, 'text/csv') || str_contains($ct, 'application/octet-stream')) {
            return;
        }

        // Skip 3xx redirects — log only actual renders and errors
        $status = $response->getStatusCode();
        if ($status >= 300 && $status < 400) {
            return;
        }

        $staff = StaffUser::find($staffUserId);
        if (!$staff) {
            return;
        }

        // Capture exception message bound by the exception handler
        $errorMessage = null;
        if ($status >= 400 && app()->bound('current.page.exception')) {
            $e = app('current.page.exception');
            $errorMessage = get_class($e) . ': ' . mb_substr($e->getMessage(), 0, 500);
        }

        UserPageLog::create([
            'staff_user_id' => $staffUserId,
            'staff_name'    => $staff->name,
            'route_name'    => $routeName,
            'url'           => mb_substr($request->fullUrl(), 0, 500),
            'method'        => $request->method(),
            'ip_address'    => $request->ip(),
            'status_code'   => $status,
            'error_message' => $errorMessage,
        ]);
    }
}
