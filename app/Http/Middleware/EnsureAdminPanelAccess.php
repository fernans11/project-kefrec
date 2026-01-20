<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('admin')->user();

        if (! $user || ! in_array($user->usertype, ['admin', 'owner'], true)) {
            auth('admin')->logout();

            return redirect()->route('home');
        }

        return $next($request);
    }
}
