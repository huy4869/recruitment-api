<?php

namespace App\Http\Middleware;

use App\Exceptions\InputException;
use App\Helpers\ResponseHelper;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\JsonResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $userRoleId = auth()->user()->role_id ?? null;

        if ($userRoleId == User::ROLE_SUB_ADMIN || $userRoleId == User::ROLE_ADMIN) {
            return $next($request);
        }

        return ResponseHelper::sendResponse(ResponseHelper::STATUS_CODE_UNAUTHORIZED, trans('response.unauthenticated'));
    }
}
