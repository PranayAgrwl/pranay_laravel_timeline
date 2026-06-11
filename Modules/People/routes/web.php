<?php

use Illuminate\Support\Facades\Route;
use Modules\People\Http\Controllers\CardDavController;
use Modules\People\Http\Controllers\PeopleController;

/*
|--------------------------------------------------------------------------
| People module - web routes
|--------------------------------------------------------------------------
| Two routing groups:
|   1) CardDAV endpoints  -  NOT behind session auth; sabre/dav does its
|      own HTTP Basic authentication. CSRF is disabled for these paths
|      in bootstrap/app.php.
|   2) Web UI endpoints  -  behind the usual auth + verified session
|      middleware (added in later steps).
*/

// 1) CardDAV (Step 3)
//
// /.well-known/carddav  -  the discovery URL DAVx5 + Apple Contacts hit
//                          first; we 301-redirect them to /dav/.
// /dav/{any?}            -  the actual DAV server (handles every HTTP
//                          method: GET, PROPFIND, REPORT, PUT, ...).
Route::get('/.well-known/carddav', [CardDavController::class, 'wellKnown']);

// Laravel's Route::any() only matches the 7 standard HTTP verbs. WebDAV
// extends HTTP with PROPFIND / REPORT / MKCOL / MOVE / etc., so we have
// to enumerate every method sabre/dav might receive.
Route::match(
    [
        // standard HTTP
        'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS',
        // WebDAV (RFC 4918)
        'PROPFIND', 'PROPPATCH', 'MKCOL', 'COPY', 'MOVE', 'LOCK', 'UNLOCK',
        // CardDAV (RFC 6352) + CalDAV/ACL extensions sabre may emit
        'REPORT', 'ACL', 'SEARCH', 'MKCALENDAR',
    ],
    '/dav/{any?}',
    [CardDavController::class, 'handle']
)->where('any', '.*');

// 2) Web UI (placeholder from scaffold - Step 4 will flesh this out)
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('people', PeopleController::class)->names('people');
});
