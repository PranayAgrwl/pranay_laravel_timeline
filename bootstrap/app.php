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
        | INGRESS PATH IN PRODUCTION
        | --------------------------
        |    public Internet (HTTPS)
        |        |
        |        v
        |    Caddy (terminates TLS, adds X-Forwarded-For/-Proto/-Host)
        |        |  HTTP over Docker bridge net (172.18.0.x)
        |        v
        |    pranay_timeline_app  (php:8.3-apache  ->  Laravel)
        |
        | Without this configuration Laravel ignores the X-Forwarded-*
        | headers Caddy sets, treats every request as plain HTTP from the
        | container's neighbour IP, and ends up issuing session/CSRF
        | cookies with attributes that don't match the public HTTPS
        | context. The visible symptom is intermittent 419 Page Expired
        | on login, and "Remember Me" failing every time.
        |
        | WHY THESE SPECIFIC CIDRs
        | ------------------------
        |  - 127.0.0.1       : the container's own loopback
        |  - 172.16.0.0/12   : entire Docker private bridge range
        |                      (covers 172.16.0.0 - 172.31.255.255,
        |                      includes the 172.18.0.0/16 net used here)
        |  - 192.168.1.0/24  : your LAN, in case Caddy is reached over the
        |                      LAN bridge by a local device
        |
        | This list deliberately MIRRORS the `trusted_proxies` block in
        | your Caddyfile so the trust boundary is identical at every hop.
        | If you ever introduce a new front-end (Cloudflare, another
        | balancer, etc.) update BOTH places to keep them in sync.
        |
        | If a new untrusted intermediary is ever introduced and you only
        | update one side, requests from it will either:
        |   - be IP-spoofable (Laravel side too loose), or
        |   - serve broken cookies again (Laravel side too tight).
        |
        | SECURITY NOTE
        | -------------
        | We do NOT use `at: '*'` even though it would also work, because
        | a future operator change (e.g. exposing port 80 on the host
        | directly to bypass Caddy) would silently let any client spoof
        | X-Forwarded-For. Specific CIDRs make that impossible.
        */
        $middleware->trustProxies(
            at: [
                '127.0.0.1',
                '172.16.0.0/12',
                '192.168.1.0/24',
            ],
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
