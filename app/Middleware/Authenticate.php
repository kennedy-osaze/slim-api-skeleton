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
    /**
     * Handle request by checking if it comes with a valid token
     *
     * @param \Psr\Http\Message\ResponseInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param  \Closure  $next
     *
     * @return mixed
     *
     * @throws \App\Exceptions\HttpException
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $token = $this->jwt->getTokenFromRequest();

        if (!$token) {
            return $response->withJson(['status' => 401, 'error' => ['message' => 'Token is required']], 401);
        }

        try {
            $user = $this->jwt->getUserByToken($token);
            $request = $request->withAttribute('user', $user);

            return $next($request, $response);
        } catch (Exception $e) {
            if ($e instanceof TokenExpiredException) {
                return $response->withJson(['status' => 401, 'error' => ['message' => 'Token has expired']]);
            }

            if ($e instanceof TokenInvalidException) {
                return $response->withJson(['status' => 400, 'error' => ['message' => 'Token is invalid']]);
            }

            throw new HttpException(500, 'An error occurred authenticating user', $e);
        }
    }
}
