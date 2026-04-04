<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Volt;
use App\Enums\UserRole;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| ROOT
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect('/login');
    }

    return redirect()->route('redirect');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->group(function () {

    Route::get('/redirect', function () {
        $user = Auth::user();
        if (!$user || !$user->role) {
            return redirect('/login')->with('error', 'User role not assigned.');
        }

        $userRole = UserRole::tryFrom(strtolower($user->role));
        if (!$userRole) {
            return redirect('/login')->with('error', 'Invalid system role.');
        }

        return redirect()->route($userRole->dashboardRoute());
    })->name('redirect');
    /*
    |--------------------------------------------------------------------------
    | ADMIN & MANAGER
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:admin,manager'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::view('/', 'dashboard')->name('dashboard');
            Route::view('/profile', 'profile')->name('profile');

            Volt::route('/users', 'admin.users.index')->name('users.index');
            Volt::route('/users/create', 'admin.users.create')->name('users.create');
            Volt::route('/users/{user}/edit', 'admin.users.edit')->name('users.edit');

            Volt::route('/deliveries/create', 'admin.deliveries.create')->name('deliveries.create');
            Volt::route('/deliveries', 'admin.deliveries.index')->name('deliveries.index');
            Volt::route('/deliveries/{order}/edit', 'admin.deliveries.edit')->name('deliveries.edit');

            Volt::route('/vehicules/create', 'admin.vehicules.create')->name('vehicules.create');
            Volt::route('/vehicules', 'admin.vehicules.index')->name('vehicules.index');
            Volt::route('/vehicules/{vehicle}/edit', 'admin.vehicules.edit')->name('vehicules.edit');

            Volt::route('/bons/create', 'admin.bons.create')->name('bons.create');
            Volt::route('/bons', 'admin.bons.index')->name('bons.index');
            Volt::route('/bons/{bon}/delete', 'admin.bons.delete')->name('bons.delete');
        });

    /*
    |--------------------------------------------------------------------------
    | DRIVER
    |--------------------------------------------------------------------------
    */
    Route::middleware(['role:driver'])
        ->prefix('driver')
        ->name('driver.')
        ->group(function () {

            Route::get('/', App\Livewire\Driver\Dashboard::class)->name('dashboard');
            Route::get('/deliveries', App\Livewire\Driver\Create::class)->name('deliveries');
            Volt::route('/profile', 'driver.profile')->name('profile');
        });

    /*
    |--------------------------------------------------------------------------
    | PROFILE PHOTO
    |--------------------------------------------------------------------------
    */
    Route::get('profile/photo/{filename}', function ($filename) {
        $path = 'profiles/' . $filename;

        if (!Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('private')->path($path));
    })->name('profile.photo');

});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/
Route::post('logout', function (Logout $logout) {
    $logout();
    return redirect('/');
})->name('logout');

require __DIR__.'/auth.php';