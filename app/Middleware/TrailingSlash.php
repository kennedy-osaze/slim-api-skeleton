<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class TrailingSlash extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $uri = $request->getUri();
        $path = $uri->getPath();

        if ($path !== '/' && substr($path, -1) === '/') {
            // permanently redirect paths with a trailing slash
            // to their non-trailing counterpart
            $uri = $uri->withPath(substr($path, 0, -1));

            return
                ($request->getMethod() === 'GET')
                ? $response->withRedirect((string) $uri, 301)
                : $next($request->withUri($uri), $response);
        }

        return $next($request, $response);
    }
}
