<?php

namespace App\Middleware;

use Exception;
use App\Exceptions\HttpException;
use Psr\Http\Message\ResponseInterface as Response;
use App\Libraries\Jwt\Exceptions\TokenExpiredException;
use App\Libraries\Jwt\Exceptions\TokenInvalidException;
use Psr\Http\Message\ServerRequestInterface as Request;

class Authenticate extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $token = $this->getRequestBearToken($request);

        if (!$token) {
            return $response->withJson(['status' => 'error', 'message' => 'Token is required'], 401);
        }

        try {
            $user = $this->jwt->getUserByToken($token);
            $request = $request->withAttribute('user', $user);

            return $next($request, $response);
        } catch (Exception $e) {
            if ($e instanceof TokenExpiredException) {
                return $response->withJson(['status' => 'error', 'message' => 'Token has expired']);
            }

            if ($e instanceof TokenInvalidException) {
                return $response->withJson(['status' => 'error', 'message' => 'Token is invalid']);
            }

            throw new HttpException(500, 'An error occurred authenticating user', $e);
        }
    }

    protected function getRequestBearToken(Request $request)
    {
        $token = $request->getHeaderLine('Authorization');

        if (!$token || substr((string) $token, 0, 6) !== 'Bearer') {
            return null;
        }

        return substr((string) $token, 7);
    }
}
