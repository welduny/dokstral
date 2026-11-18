<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LoginOriginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_form_posts_to_the_current_origin(): void
    {
        $this->withServerVariables([
            'HTTP_HOST' => '127.0.0.1:8000',
            'SERVER_NAME' => '127.0.0.1',
            'SERVER_PORT' => 8000,
        ])->get('/login')
            ->assertOk()
            ->assertSee('action="/login"', false)
            ->assertDontSee('action="http://localhost/login"', false);
    }

    public function test_guest_navigation_does_not_show_member_loans_and_loan_page_requires_login(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertDontSee('Peminjaman saya')
            ->assertSee('>Masuk</a>', false)
            ->assertSee('>Daftar</a>', false);

        $this->get(route('loans.mine'))
            ->assertRedirect(route('login'));
    }

    public function test_active_member_navigation_shows_their_loans_link(): void
    {
        $member = User::factory()->create(['role' => 'siswa', 'status' => 'active']);

        $this->actingAs($member)
            ->get('/')
            ->assertOk()
            ->assertSee('href="'.route('loans.mine').'"', false)
            ->assertSee('Peminjaman saya');
    }

    public function test_login_form_can_authenticate_an_active_user(): void
    {
        $user = User::factory()->create([
            'email' => 'reader@example.com',
            'password' => 'reader-password',
            'status' => 'active',
        ]);

        $this->get('/login')->assertOk();
        $this->post('/login', [
            'email' => $user->email,
            'password' => 'reader-password',
        ])->assertRedirect('/home');

        $this->assertAuthenticatedAs($user);
    }

    public function test_pending_member_with_correct_password_is_told_to_wait_for_approval(): void
    {
        $user = User::factory()->create([
            'email' => 'pending-reader@example.com',
            'password' => 'reader-password',
            'status' => 'pending',
        ]);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'reader-password',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors([
                'email' => 'Akun kamu masih menunggu persetujuan admin. Coba masuk kembali setelah akun disetujui.',
            ]);

        $this->assertGuest();
    }

    public function test_pending_member_with_wrong_password_gets_generic_login_error(): void
    {
        $user = User::factory()->create([
            'email' => 'pending-reader@example.com',
            'password' => 'reader-password',
            'status' => 'pending',
        ]);

        $this->from('/login')->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email', trans('auth.failed'));
    }

    public function test_admin_login_form_posts_to_the_current_origin(): void
    {
        $this->withServerVariables([
            'HTTP_HOST' => '127.0.0.1:8000',
            'SERVER_NAME' => '127.0.0.1',
            'SERVER_PORT' => 8000,
        ])->get('/admin/login')
            ->assertOk()
            ->assertSee('action="/admin/login"', false);
    }

    public function test_user_login_form_does_not_repopulate_email_or_autofill_credentials(): void
    {
        $this->from('/login')->post('/login', [
            'email' => 'previous@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect('/login');

        $this->get('/login')
            ->assertOk()
            ->assertDontSee('value="previous@example.test"', false)
            ->assertSee('name="email" required autocomplete="off"', false)
            ->assertSee('name="password" required autocomplete="new-password"', false);
    }

    public function test_admin_login_form_does_not_repopulate_email_or_autofill_credentials(): void
    {
        $this->from('/admin/login')->post('/admin/login', [
            'email' => 'previous-admin@example.test',
            'password' => 'wrong-password',
        ])->assertRedirect('/admin/login');

        $this->get('/admin/login')
            ->assertOk()
            ->assertDontSee('value="previous-admin@example.test"', false)
            ->assertSee('name="email" required autocomplete="off"', false)
            ->assertSee('name="password" required autocomplete="new-password"', false);
    }

    public function test_expired_login_csrf_token_redirects_to_a_fresh_login_form(): void
    {
        $request = Request::create(route('login'), 'POST');
        $request->setLaravelSession(Session::driver());
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new HttpException(419));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->headers->get('Location'));
        $this->assertSame('Formulir sudah kedaluwarsa. Silakan coba lagi.', $request->session()->get('error'));
    }

    public function test_password_reset_page_has_one_same_origin_code_form(): void
    {
        $response = $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('action="/password/email"', false)
            ->assertSee('name="code"', false)
            ->assertSee('name="password_confirmation"', false)
            ->assertDontSee('Kirim tautan reset');

        $html = $response->getContent();
        $this->assertSame(1, substr_count($html, '<form'));
        $this->assertSame(1, substr_count($html, '</form>'));
        $this->get('/password/reset/test-token')->assertNotFound();
    }

    public function test_password_email_and_confirm_forms_use_relative_actions(): void
    {
        $this->get(route('password.request'))
            ->assertOk()
            ->assertSee('action="/password/email"', false);

        $user = User::factory()->create(['status' => 'active']);
        $this->actingAs($user)
            ->get(route('password.confirm'))
            ->assertOk()
            ->assertSee('action="/password/confirm"', false);
    }
}