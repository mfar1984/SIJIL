<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        /*
        | The API group was empty, so nothing throttled the API and the kill
        | switch and Rate Limit setting on Settings > Global Config > API &
        | Integrations had no effect. 24 of 30 routes ran with no limit at all,
        | four of them public.
        |
        | app/Http/Kernel.php used to declare "throttle:api" on this group and was
        | presumably believed to be doing this job. Laravel has not read that file
        | since version 11, and the "api" limiter it named was never defined, so
        | the group it described never ran. That file has been removed.
        |
        | Routes that set their own tighter throttle keep it: a second throttle
        | middleware applies in addition to this one rather than replacing it, so
        | participant/login stays capped at its deliberate 8 per minute.
        */
        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureApiIsEnabled::class,
        ]);

        $middleware->throttleApi('api');

        /*
        | Response headers the app never sent. Applied globally so downloads,
        | public certificate pages and the API all get them.
        */
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);

        /*
        | Account status used to be checked only while signing in, so banning
        | someone had no effect until their session lapsed. This re-checks on
        | every session request, and enforces password expiry.
        */
        $middleware->web(append: [
            \App\Http\Middleware\EnsureAccountIsUsable::class,
            /*
            | Honours the Maintenance Mode switch on the General tab, which was
            | stored and read by nothing. Sign-in and certificate verification stay
            | open, and an administrator is never blocked, so the switch cannot lock
            | out the only person able to turn it off.
            */
            \App\Http\Middleware\MaintenanceMode::class,
        ]);

        $middleware->alias([
            'api.key' => \App\Http\Middleware\AuthenticateApiKey::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
        | Honours "Notify administrators of system errors" on the Notifications
        | tab, which was stored and read by nothing.
        |
        | Reporting only, so the response the visitor receives is unchanged. The
        | notifier suppresses repeats of the same error for half an hour, because
        | a broken page raises the same exception on every request.
        */
        $exceptions->report(function (\Throwable $e) {
            \App\Support\AdminNotifier::systemError($e);
        });
    })->create();
