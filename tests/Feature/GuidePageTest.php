<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuidePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_is_public_and_explains_member_flows(): void
    {
        $this->get(route('guide'))
            ->assertOk()
            ->assertSee('PANDUAN LEHBOOK')
            ->assertSee('Untuk anggota')
            ->assertSee('Pengembalian & denda', false)
            ->assertSee('tunjukkan kode 5 digit di bawah QR kepada petugas perpustakaan')
            ->assertSee('href="'.route('guide').'"', false);
    }

    public function test_guide_is_not_available_to_admins(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);

        $this->actingAs($admin)
            ->get(route('guide'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee('href="'.route('guide').'"', false)
            ->assertDontSee('Panduan</a>', false);
    }
}
