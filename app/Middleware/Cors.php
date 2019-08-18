<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class Cors extends Middleware
{
    /**
     * Handle Cross-Site Request Sharing (CORS) on the server side
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Closure $next
     *
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        if (!$this->isCorRequest($request)) {
            return $next($request, $response);
        }

        if (!$this->isRequestAllowed($request)) {
            return $response->withJson(['status' => 'error', 'message' => 'CORS Forbidden'], 403);
        }

        $settings = $this->settings['cors'];

        $allowed_origin = (in_array('*', $settings['allowed_origin'])) ? '*' : $request->getHeaderLine('Origin');
        $response->withHeader('Access-Control-Allow-Origin', $allowed_origin);

        if ($allowed_origin !== '*' && $settings['credentials'] === true) {
            $response->withHeader('Access-Control-Allow-Credentials', true);
        }

        // Check request is a preflight request
        if ($request->isOptions()) {
            return $response
                ->withHeader('Access-Control-Allow-Methods', implode(', ', $settings['methods']))
                ->withHeader('Access-Control-Allow-Headers', implode(', ', $settings['allow_headers']))
                ->withHeader('Access-Control-Max-Age', $settings['max_age'])
                ->withStatus(204, 'Preflight OK');
        }

        $response = $next($request, $response);

        return $response
            ->withHeader('Access-Control-Expose-Headers', implode(', ', $settings['expose_headers']));
    }

    /**
     * Checks if a request is a CORS request, that is, request is made to another origin
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isCorRequest(Request $request)
    {
        if (!$request->hasHeader('Origin')) {
            return false;
        }

        return !in_array($request->getUri()->getBaseUrl(), $request->getHeader('Origin'));
    }

    /**
     * Checks if a request is allowed based on the allowed methods and origins set in the config setting file
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return bool
     */
    protected function isRequestAllowed(Request $request)
    {
        $settings = $this->settings['cors'];

        if (!in_array($request->getMethod(), $settings['methods'])) {
            return false;
        }

        if (in_array('*', $settings['origin'])) {
            return true;
        }

        $matches = array_filter($settings['origin'], function ($origin) use ($request) {
            return fnmatch($origin, $request->getHeaderLine('Origin'));
        });

        return $matches > 0;
    }
}
