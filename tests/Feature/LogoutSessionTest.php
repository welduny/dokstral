<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class LogoutSessionTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_with_an_expired_csrf_token_redirects_instead_of_showing_419(): void
    {
        $user = User::factory()->create(['status' => 'active']);
        Auth::login($user);
        $request = Request::create(route('logout'), 'POST');
        $request->setLaravelSession(Session::driver());
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new HttpException(419));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('login'), $response->headers->get('Location'));
        $this->assertSame('Sesi kamu sudah diperbarui. Silakan masuk kembali.', $request->session()->get('status'));
        $this->assertFalse(Auth::check());
    }

    public function test_csrf_errors_on_non_logout_routes_are_not_redirected(): void
    {
        $request = Request::create('/password/email', 'POST');
        $request->setLaravelSession(Session::driver());
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new HttpException(419));

        $this->assertSame(419, $response->getStatusCode());
    }

    public function test_expired_admin_return_csrf_token_redirects_to_admin_login(): void
    {
        $request = Request::create('/admin/loans/5/return', 'POST');
        $request->setLaravelSession(Session::driver());
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));

        $response = app(\Illuminate\Contracts\Debug\ExceptionHandler::class)
            ->render($request, new HttpException(419));

        $this->assertSame(302, $response->getStatusCode());
        $this->assertSame(route('admin.login'), $response->headers->get('Location'));
        $this->assertSame('Formulir sudah kedaluwarsa. Silakan coba lagi.', $request->session()->get('error'));
    }
}