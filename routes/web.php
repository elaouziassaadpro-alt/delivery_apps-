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

            Volt::route('/bons/create', 'admin.bons.clients.create')->name('bons.client.create');
            Volt::route('/bons', 'admin.bons.clients.index')->name('bons.client.index');
            Volt::route('/bons/{bon}', 'admin.bons.clients.show')->name('bons.client.show');
            Volt::route('/bons/{bon}/edit', 'admin.bons.clients.edit')->name('bons.client.edit');
            Volt::route('/bons/{bon}/delete', 'admin.bons.clients.delete')->name('bons.client.delete');

            Volt::route('/bons-driver/create', 'admin.bons.drivers.create')->name('bons.driver.create');
            Volt::route('/bons-driver', 'admin.bons.drivers.index')->name('bons.driver.index');
            Volt::route('/bons-driver/{bon}', 'admin.bons.drivers.show')->name('bons.driver.show');
            Volt::route('/bons-driver/{bon}/edit', 'admin.bons.drivers.edit')->name('bons.driver.edit');
            Volt::route('/bons-driver/{bon}/delete', 'admin.bons.drivers.delete')->name('bons.driver.delete');
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
            Volt::route('/profile', 'driver.profile')->name('profile');
            Volt::route('/bons', 'driver.bon.index')->name('bons.index');
            Volt::route('/bons/{bon}', 'driver.bon.show')->name('bons.show');
            Volt::route('/bons/{bon}/edit', 'driver.bon.edit')->name('bons.edit');
            Volt::route('/bons/{bon}/delete', 'driver.bon.delete')->name('bons.delete');

            Volt::route('/orders', 'driver.order.index')->name('orders.index');
            Volt::route('/orders/{order}', 'driver.order.show')->name('orders.show');
            

            


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