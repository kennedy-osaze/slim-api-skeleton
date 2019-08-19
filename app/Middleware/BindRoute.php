<?php

namespace App\Middleware;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class BindRoute extends Middleware
{
    /**
     * Handle request by binding route parameters to registered route bindings
     * That is, bindings bootstrapped in the app registry
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Closure $next
     *
     * @return mixed
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $route = $request->getAttribute('route');

        if ($route) {
            // Get route bindings registered in the application
            $route_bindings = $this->container->has('route-bindings')
                ? $this->container->get('route-bindings')
                : [];

            collect($route_bindings)
                ->filter(function ($model, $parameter) use ($route) {

                    // Filter route bindings by parameters used by the route
                    return
                        !is_null($route->getArgument($parameter))
                        && (is_callable($model) || class_exists((string) $model));
                })
                ->each(function ($model, $parameter) use ($route) {
                    // Get that value of the route parameter
                    $identifier = $route->getArgument($parameter);

                    // Resolve the parameter binding
                    $binder = is_callable($model) ? call_user_func($model, $identifier) : $model::findOrFail($identifier);

                    // Replace the route parameter value with the resolved binding
                    $route->setArgument($parameter, $binder);

                    // Still make the original parameter value available but now appended with "_id"
                    $route->setArgument(sprintf('%s_id', $parameter), $identifier);
                });
        }

        return $next($request, $response);
    }
}
