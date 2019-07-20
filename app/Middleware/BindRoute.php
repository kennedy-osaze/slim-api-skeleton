<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class BindRoute extends Middleware
{
    public function __invoke(Request $request, Response $response, $next)
    {
        $route = $request->getAttribute('route');

        if ($route) {
            $route_bindings = $this->container->has('route-bindings')
                ? $this->container->get('route-bindings')
                : [];

            collect($route_bindings)
                ->filter(function ($model, $parameter) use ($route) {
                    return
                        !is_null($route->getArgument($parameter))
                        && (is_callable($model) || class_exists((string) $model));
                })
                ->each(function ($model, $parameter) use ($route) {
                    $identifier = $route->getArgument($parameter);
                    $binder = is_callable($model) ? call_user_func($model, $identifier) : $model::findOrFail($identifier);

                    $route->setArgument($parameter, $binder);
                    $route->setArgument(sprintf('%s_id', $parameter), $identifier);
                });
        }

        return $next($request, $response);
    }
}
