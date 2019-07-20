<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Exceptions\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

error_reporting(E_ALL);

set_error_handler(function ($level, $message, $file = '', $line = 0) {
    if (error_reporting() & $level) {
        throw new \ErrorException($message, 0, $level, $file, $line);
    }
});

return function (App $app) {
    $container = $app->getContainer();

    $container['httpExceptionHandler'] = function ($container) {
        return function (Request $request, Response $response, HttpException $exception) use ($container) {
            $error_data = [
                'status' => $exception->getStatusCode(),
                'error' => ['message' => $exception->getMessage() ?: 'An error occurred']
            ];

            $response = $response->withJson($error_data, $exception->getStatusCode());

            foreach ($exception->getHeaders() as $name => $value) {
                $response = $response->withAddedHeader($name, $value);
            }

            return $response;
        };
    };

    $container['notFoundHandler'] = function ($container) {
        return function (Request $request, Response $response) use ($container) {
            return $container['httpExceptionHandler']($request, $response, new HttpException(404, 'Resource not found.'));
        };
    };

    $container['notAllowedHandler'] = function ($container) {
        return function (Request $request, Response $response, array $methods) use ($container) {
            return $container['httpExceptionHandler'](
                $request,
                $response,
                new HttpException(405, 'Method not Allowed.', null, ['Allow' => strtoupper(implode(', ', $methods))])
            );
        };
    };

    $container['errorHandler'] = function ($container) {
        return function (Request $request, Response $response, Throwable $throwable) use ($container) {
            if ($throwable instanceof ModelNotFoundException) {
                return $container['notFoundHandler']($request, $response);
            }

            if ($throwable instanceof HttpException) {
                return $container['httpExceptionHandler']($request, $response, $throwable);
            }

            $container->logger->error($throwable);

            $error_data = ['error' => 'Internal Server Error.'];

            ($container->settings['displayErrorDetails']) and $error_data['details'] = [
                'message' => sprintf(
                    '%s : %s in %s (%s)',
                    get_class($throwable),
                    $throwable->getMessage(),
                    $throwable->getFile(),
                    $throwable->getLine()
                ),
                'trace' => $throwable->getTrace()
            ];

            return $response->withJson($error_data, 500);
        };
    };

    $container['phpErrorHandler'] = function ($container) {
        return $container['errorHandler'];
    };
};
