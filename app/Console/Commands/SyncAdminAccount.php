<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SyncAdminAccount extends Command
{
    protected $signature = 'admin:sync';
    protected $description = 'Create or update the admin account from ADMIN_EMAIL and ADMIN_PASSWORD';

    public function handle(): int
    {
        $email = config('admin.email');
        $password = config('admin.password');

        if (!$email || !$password || $password === 'password' || strlen($password) < 12) {
            $this->error('Set ADMIN_EMAIL and a unique ADMIN_PASSWORD of at least 12 characters in .env.');

            return self::FAILURE;
        }

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin Perpus',
                'nama' => 'Admin Perpus',
                'password' => Hash::make($password),
                'role' => 'admin',
                'status' => 'active',
            ]
        );

        $this->info('Admin account is ready.');

        return self::SUCCESS;
    }
}