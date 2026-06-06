<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |----------------------------------------------------------------------
        | Trusted Proxies
        |----------------------------------------------------------------------
        |
        | This app runs behind a reverse proxy in production (Docker bridge
        | network -> nginx/traefik -> php-fpm). Without telling Laravel which
        | upstream peers are trusted, the framework ignores X-Forwarded-* and
        | thinks every request is plain HTTP coming from the proxy container's
        | internal IP (e.g. 172.18.0.11). That breaks three things at once:
        |
        |   1. request()->ip() returns the proxy IP, not the real client IP.
        |   2. request()->isSecure() returns false on real HTTPS hits, so
        |      Laravel issues session/CSRF cookies WITHOUT the Secure flag,
        |      while the browser is on HTTPS - SameSite drift then drops
        |      the cookie on next POST and we get a 419 Page Expired.
        |   3. URL::current() and form `action` URLs come out as http://...
        |      even though the page is https://..., which crashes Remember
        |      Me cookies (their __Host-* prefix requires HTTPS).
        |
        | We trust ALL upstream peers (`*`) because the only thing in front
        | of this app is YOUR own ingress (nginx/traefik). If a third-party
        | CDN or shared proxy were ever introduced, tighten this to specific
        | CIDR ranges, e.g. ['172.16.0.0/12', '10.0.0.0/8'].
        |
        | The HEADER_X_FORWARDED_FOR | ... bitmask tells Laravel to honour
        | the standard set of forwarded headers but NOT the AWS ELB-only
        | header (HEADER_X_FORWARDED_AWS_ELB), which would be a smaller
        | attack surface than just trusting "all forwarded headers".
        */
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PORT
                | Request::HEADER_X_FORWARDED_PROTO
                | Request::HEADER_X_FORWARDED_AWS_ELB,
        );
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
