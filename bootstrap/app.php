<?php

use App\Http\Middleware\EnsureStaff;
use App\Http\Middleware\EnsureStudent;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\RedirectIfAuthenticatedStaff;
use App\Http\Middleware\RedirectIfAuthenticatedStudent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->domain(env('ADMIN_DOMAIN'))
                ->group(base_path('routes/admin.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->redirectGuestsTo(function (Request $request) {
            $adminDomain = config('domains.admin');

            if ($adminDomain !== null && $adminDomain !== '' && $request->getHost() === $adminDomain) {
                return route('admin.login');
            }

            return route('student.login');
        });

        $middleware->alias([
            'staff' => EnsureStaff::class,
            'student' => EnsureStudent::class,
            'guest.student' => RedirectIfAuthenticatedStudent::class,
            'guest.staff' => RedirectIfAuthenticatedStaff::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $message = 'The upload is too large for PHP (post_max_size is '
                .ini_get('post_max_size')
                .'). Increase post_max_size and upload_max_filesize in php.ini — see docs/phase-3b-setup.md.';

            if ($request->header('X-Inertia')) {
                return redirect()->back()->with('error', $message);
            }

            return null;
        });

        $exceptions->respond(function (Response $response, Throwable $exception, Request $request) {
            if (
                $request->header('X-Inertia')
                && $response->getStatusCode() >= 500
            ) {
                $message = 'Save failed. If you uploaded a large video, try a smaller file or increase PHP memory_limit (see docs/phase-3b-setup.md).';

                return redirect()->back()->with('error', $message);
            }

            return $response;
        });
    })->create();
