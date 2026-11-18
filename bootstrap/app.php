<?php

use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\EnsureActiveAccount;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'account.active' => EnsureActiveAccount::class,
        ]);
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('loans:maintain')->hourly();
        $schedule->command('loans:send-due-reminders')->dailyAt('08:00');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (HttpException $exception, Request $request) {
            if ($exception->getStatusCode() !== 419) {
                return null;
            }

            if ($request->routeIs('logout')) {
                Auth::guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->with('status', 'Sesi kamu sudah diperbarui. Silakan masuk kembali.');
            }

            $formRoute = match (true) {
                $request->isMethod('post') && $request->is('login') => 'login',
                $request->routeIs('admin.login.store') => 'admin.login',
                $request->routeIs('register') => 'register',
                $request->routeIs('admin.loans.return.process', 'admin.loans.status', 'admin.loans.destroy', 'admin.loans.confirm-handoff', 'admin.books.store', 'admin.books.update', 'admin.fines.paid', 'admin.reports.monthly', 'admin.users.update', 'admin.users.approve', 'admin.users.reject', 'admin.users.password-reset-code', 'admin.users.destroy') => 'admin.login',
                $request->routeIs('loans.return', 'loans.store') => 'login',
                default => null,
            };

            if ($formRoute === null) {
                return null;
            }

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route($formRoute)->with('error', 'Formulir sudah kedaluwarsa. Silakan coba lagi.');
        });
    })->create();
