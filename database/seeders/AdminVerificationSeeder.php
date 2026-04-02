<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Carbon;

class AdminVerificationSeeder extends Seeder
{
    /**
     * Run the database seeds to ensure all admin/manager accounts are verified.
     */
    public function run(): void
    {
        User::whereIn('role', ['admin', 'manager'])
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => Carbon::now()]);
            
        $this->command->info('All Admin and Manager accounts have been marked as verified.');
    }
}
