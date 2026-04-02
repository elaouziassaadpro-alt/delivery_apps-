<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardRoleController extends Controller
{
    public function redirect(): RedirectResponse
{
    $user = Auth::user();
    
    // 1. Check if user or role is null immediately
    if (!$user || !$user->role) {
        abort(403, 'User role not assigned.');
    }

    // 2. Normalize the role string to lowercase to match typical Enum values
    $roleValue = strtolower($user->role);

    // 3. Use tryFrom safely
    $userRole = UserRole::tryFrom($roleValue);

    if (!$userRole) {
        // Log this error so you can see exactly what string is failing in production
        \Illuminate\Support\Facades\Log::error("Invalid role detected: " . $user->role);
        abort(403, 'Access denied: Invalid role configuration.');
    }
    return redirect()->route($userRole->dashboardRoute());
}
}