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

        // Check request is a preflight request
        if ($request->isOptions()) {
            return $this->responseWithPreflightHeaders($request, $response);
        }

        $response = $next($request, $response);

        return $this->responseWithCorsHeaders($request, $response);
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

        if (!in_array($request->getMethod(), $settings['allowed_methods'])) {
            return false;
        }

        if (in_array('*', $settings['allowed_origins'])) {
            return true;
        }

        $matches = array_filter($settings['allowed_origins'], function ($origin) use ($request) {
            return fnmatch($origin, $request->getHeaderLine('Origin'));
        });

        return $matches > 0;
    }

    /**
     * Returns the response with the HTTP headers required to handle Preflight requests
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function responseWithPreflightHeaders(Request $request, Response $response)
    {
        $settings = $this->settings['cors'];

        if ($settings['credentials']) {
            $response  = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->allowedOrigin($request))
            ->withHeader('Access-Control-Allow-Methods', implode(', ', $settings['allowed_methods']))
            ->withHeader('Access-Control-Allow-Headers', implode(', ', $settings['allow_headers']))
            ->withHeader('Access-Control-Max-Age', $settings['max_age'])
            ->withStatus(204, 'Preflight OK');
    }

    /**
     * Returns the response with the HTTP headers required to handle CORS requests
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function responseWithCorsHeaders(Request $request, Response $response)
    {
        $settings = $this->settings['cors'];

        if ($settings['credentials']) {
            $response  = $response->withHeader('Access-Control-Allow-Credentials', 'true');
        }

        return $response
            ->withHeader('Access-Control-Allow-Origin', $this->allowedOrigin($request))
            ->withHeader('Access-Control-Expose-Headers', implode(', ', $settings['expose_headers']));
    }

    /**
     * Returns the allowed origin string
     *
     * @param Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     */
    protected function allowedOrigin(Request $request)
    {
        $settings = $this->settings['cors'];

        return (in_array('*', $settings['allowed_origins']) && !$settings['credentials'])
            ? '*'
            : $request->getHeaderLine('Origin');
    }
}
