<?php

namespace App\Http\Middleware;

use App\Exceptions\InputException;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class UserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     * @throws InputException
     */
    public function handle(Request $request, Closure $next)
    {
        $userRoleId = auth()->user()->role_id;

        if ($userRoleId == User::ROLE_USER) {
            return $next($request);
        }

        throw new InputException(trans('response.not_found'));
    }
}
