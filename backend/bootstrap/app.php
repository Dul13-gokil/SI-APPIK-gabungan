<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
        ->withRouting(
            web: __DIR__.'/../routes/web.php',
            api: __DIR__.'/../routes/api.php',
            commands: __DIR__.'/../routes/console.php',
            health: '/up',
        )
        ->withMiddleware(function (Middleware $middleware): void {
            $middleware->trustProxies(at: '*');
            $middleware->alias([
                'role'   => \App\Http\Middleware\EnsureUserHasRole::class,
                'active' => \App\Http\Middleware\EnsureUserIsActive::class,
            ]);
            $middleware->web(append: [
                \App\Http\Middleware\SecurityHeadersMiddleware::class,
            ]);
            $middleware->api(append: [
                \App\Http\Middleware\SecurityHeadersMiddleware::class,
            ]);
            $middleware->validateCsrfTokens(except: [
                'api/*',
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Pelanggaran aturan bisnis yang sudah ditangani handler di bawah tidak perlu masuk log error.
        $exceptions->dontReport([
            \App\Domain\Complaints\Exceptions\InvalidStatusTransitionException::class,
            \App\Domain\Complaints\Exceptions\FieldTaskAuthorizationException::class,
            \App\Domain\Complaints\Exceptions\PhotoRadiusException::class,
        ]);

        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, Request $request) {
            return redirect()->route('login')->with('info', 'Sesi formulir Anda telah disegarkan. Silakan coba masuk kembali.');
        });

        // Transisi status ilegal -> 422, bukan 500.
        $exceptions->render(function (\App\Domain\Complaints\Exceptions\InvalidStatusTransitionException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                    'data'    => null,
                    'meta'    => ['timestamp' => now()->toIso8601String()],
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        });

        // Foto bukti di luar radius lokasi aduan -> 422.
        $exceptions->render(function (\App\Domain\Complaints\Exceptions\PhotoRadiusException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                    'data'    => null,
                    'meta'    => ['timestamp' => now()->toIso8601String()],
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        });

        // Pelanggaran team shield (403) dan kuota tugas (422) dari FieldTaskAuthorizer.
        $exceptions->render(function (\App\Domain\Complaints\Exceptions\FieldTaskAuthorizationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage(),
                    'data'    => null,
                    'meta'    => ['timestamp' => now()->toIso8601String()],
                ], $e->statusCode);
            }

            return back()->with('error', $e->getMessage());
        });
    })->create();

// Satu berkas .env untuk seluruh proyek: <root repo>/.env, bukan backend/.env.
// Path diarahkan sebelum environment dimuat, jadi artisan/tinker di host dan container
// memakai nilai yang sama.
$rootEnv = dirname(__DIR__, 2) . '/.env';
if (is_file($rootEnv)) {
    $app->useEnvironmentPath(dirname($rootEnv));
}

return $app;
