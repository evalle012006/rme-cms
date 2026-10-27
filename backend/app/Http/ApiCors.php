<?php

namespace App\Http;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Normalises CORS headers on every API response, including ones the route
 * middleware never sees.
 *
 * The locality endpoints send Cache-Control: public for a day. The CORS
 * package only adds Access-Control-Allow-Origin when the request already
 * carries an Origin, and it echoes that origin. The public site and the CMS
 * are different subdomains of the same site, so they share a browser cache.
 * Whichever response was stored first is reused: a same-origin copy has no
 * CORS header, and a copy echoed for the other subdomain fails the check.
 * That shows up as an intermittent CORS error on /api/localities/states.
 *
 * A wildcard origin does not vary between callers, so one cached copy stays
 * valid. Browsers reject that wildcard when credentials are enabled, and this
 * API authenticates with bearer tokens, so credentials are left off.
 *
 * Applied from the RequestHandled event rather than middleware. Preflight is
 * answered by the CORS package before routing, and throttle / 404 / 500
 * responses are rendered after the middleware stack has unwound.
 */
class ApiCors
{
    /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public static function apply(Request $request, Response $response)
    {
        if (!self::appliesTo($request)) {
            return $response;
        }

        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->remove('Access-Control-Allow-Credentials');

        return $response;
    }

    /**
     * @param Request $request
     * @return bool
     */
    private static function appliesTo(Request $request)
    {
        $paths = config('cors.paths', ['api/*']);

        foreach ((array) $paths as $path) {
            if ($request->is($path)) {
                return true;
            }
        }

        return false;
    }
}
