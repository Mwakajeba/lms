<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class EnsureBranchSelected
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        // Only check for authenticated users
        if ($user && !$user->branch_id) {
            // Only redirect if not already on branch selection page
            if (!$request->is('select-branch')) {
                return redirect()->route('select-branch');
            }
        }
        return $next($request);
    }
}
