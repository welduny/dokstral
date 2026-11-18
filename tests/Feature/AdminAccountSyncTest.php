<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminAccountSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sync_activates_the_configured_account_with_a_strong_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.test',
            'role' => 'admin',
            'status' => 'inactive',
        ]);
        Config::set('admin.email', 'admin@example.test');
        Config::set('admin.password', 'a-unique-strong-admin-password');

        $this->assertSame(0, Artisan::call('admin:sync'));

        $this->assertSame('active', $user->fresh()->status);
        $this->assertTrue(Hash::check('a-unique-strong-admin-password', $user->fresh()->password));
    }

    public function test_admin_sync_rejects_missing_or_default_passwords(): void
    {
        Config::set('admin.email', 'admin@example.test');
        Config::set('admin.password', 'password');

        $this->assertSame(1, Artisan::call('admin:sync'));
        $this->assertDatabaseMissing('users', ['email' => 'admin@example.test']);
    }
}