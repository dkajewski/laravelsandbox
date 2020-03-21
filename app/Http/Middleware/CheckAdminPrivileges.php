<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdminPrivileges
{
    const ADMIN = 'Admin';

    /**
     * Check if user is allowed to see admin panel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // todo: Check user permissions instead of group name
        foreach ($request->user()->groups as $group) {
            if ($group->name === self::ADMIN) {
                return $next($request);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'basic.admin-privileges-not-found',
        ]);
    }
}
