<?php

namespace Modules\People\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\People\CardDav\DavServer;
use Sabre\DAV\Exception as SabreDavException;
use Sabre\DAV\Server as SabreServer;
use Sabre\HTTP\Request as SabreRequest;
use Sabre\HTTP\Response as SabreResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

/**
 * CardDavController
 *
 * The single entry-point for everything under /dav/* . Bridges the
 * incoming Laravel/Symfony request into a sabre/dav request, runs
 * sabre's pipeline (auth -> ACL -> CardDAV -> Sync), then ports the
 * sabre response back into a Symfony response that Laravel can return.
 *
 * Why not let sabre talk to PHP's SAPI directly:
 *   - sabre uses raw header() / echo, which conflicts with Laravel's
 *     middleware post-processing and response collection.
 *   - capturing the response here keeps sabre invisible to the rest
 *     of the Laravel stack (logging, error handling, etc.).
 *
 * CSRF: the /dav/* and /.well-known/carddav routes are excluded from
 * CSRF validation in bootstrap/app.php (DAVx5 doesn't speak CSRF
 * tokens; Basic auth is its only credential).
 */
class CardDavController extends Controller
{
    /**
     * Handle the discovery redirect at /.well-known/carddav -> /dav/.
     * Apple's spec and most clients (including DAVx5) follow it.
     */
    public function wellKnown(): SymfonyResponse
    {
        return redirect('/dav/', 301);
    }

    /**
     * Handle every method (GET, PROPFIND, REPORT, PUT, ...) under
     * /dav/* by handing the request to sabre/dav.
     */
    public function handle(Request $request): SymfonyResponse
    {
        $sabreRequest = $this->toSabreRequest($request);

        // CRITICAL: tell sabre what the *prefix* of the URL is so its
        // tree walker strips it before resolving path -> resource.
        // Without this, sabre would look for a node named "dav" in
        // the tree root and 404 on every request after the redirect.
        $sabreRequest->setBaseUrl('/dav/');

        $sabreResponse = new SabreResponse();

        $server = (new DavServer())->build();
        $server->setBaseUri('/dav/');           // used for <d:href> emission
        $server->httpRequest = $sabreRequest;
        $server->httpResponse = $sabreResponse;

        // sabre throws Sabre\DAV\Exception subclasses to signal HTTP errors
        // (NotAuthenticated -> 401, Forbidden -> 403, NotFound -> 404, ...).
        // sabre's start() would catch and convert them, but invokeMethod()
        // does not - we have to replicate that conversion here.
        try {
            // false = don't auto-send the response; we'll bridge it
            // back through Symfony below.
            $server->invokeMethod($sabreRequest, $sabreResponse, false);
        } catch (SabreDavException $e) {
            $this->writeSabreExceptionToResponse($server, $sabreResponse, $e);
        } catch (Throwable $e) {
            // Anything else is genuinely unexpected - rethrow so Laravel's
            // normal exception handler logs + renders it.
            throw $e;
        }

        return $this->toSymfonyResponse($sabreResponse);
    }

    /**
     * Replicate sabre's own exception->response serialisation so that
     * NotAuthenticated yields a proper 401 + WWW-Authenticate, etc.
     * Mirrors the logic in Sabre\DAV\Server::start()'s catch block.
     */
    private function writeSabreExceptionToResponse(SabreServer $server, SabreResponse $response, SabreDavException $e): void
    {
        $dom = new \DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;
        $errorRoot = $dom->createElementNS('DAV:', 'd:error');
        $errorRoot->setAttribute('xmlns:s', SabreServer::NS_SABREDAV);
        $dom->appendChild($errorRoot);

        $e->serialize($server, $errorRoot);

        $headers = $e->getHTTPHeaders($server);
        $headers['Content-Type'] = 'application/xml; charset=utf-8';

        $response->setStatus($e->getHTTPCode());
        $response->setHeaders($headers);
        $response->setBody($dom->saveXML());
    }

    /**
     * Convert the Laravel/Symfony request into a sabre/http Request.
     * Header casing differences between the two libraries are normalised.
     */
    private function toSabreRequest(Request $request): SabreRequest
    {
        $headers = [];
        foreach ($request->headers->all() as $name => $values) {
            $headers[$name] = $values;
        }

        // sabre needs the absolute path INCLUDING the /dav prefix,
        // not the relative path Laravel hands us.
        $url = $request->getPathInfo();
        if ($request->getQueryString() !== null) {
            $url .= '?'.$request->getQueryString();
        }

        return new SabreRequest(
            $request->getMethod(),
            $url,
            $headers,
            $request->getContent(true) // (true) = return as stream resource
        );
    }

    /**
     * Convert a sabre/http Response back into a Symfony Response.
     */
    private function toSymfonyResponse(SabreResponse $sabreResponse): SymfonyResponse
    {
        $body = $sabreResponse->getBodyAsString();

        $response = new SymfonyResponse(
            $body,
            $sabreResponse->getStatus(),
        );

        // sabre's getHeaders() returns each header as an array of strings;
        // Symfony's HeaderBag accepts both forms - pass through as-is.
        foreach ($sabreResponse->getHeaders() as $name => $values) {
            $response->headers->set($name, $values, true);
        }

        return $response;
    }
}
