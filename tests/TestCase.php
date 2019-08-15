<?php

namespace Tests;

use Slim\App;
use Carbon\Carbon;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\Environment;
use Faker\Factory as Faker;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $app;

    protected $faker;

    protected $withMiddleware = true;

    protected function setUp()
    {
        parent::setUp();

        $this->createApplication();

        $this->setUpTraits();

        $this->faker = Faker::create();
    }

    protected function createApplication()
    {
        $config = require __DIR__ . '/../config/index.php';

        $app = new App(['settings' => $config]);

        $dependencies = require __DIR__ . '/../bootstrap/dependencies.php';
        $dependencies($app);

        $registry = require __DIR__ . '/../bootstrap/registry.php';
        $registry($app);

        $error_handlers = require __DIR__ . '/../bootstrap/error-handlers.php';
        $error_handlers($app);

        if ($this->withMiddleware) {
            $middleware = require __DIR__ . '/../bootstrap/middleware.php';
            $middleware($app, $this->withoutMiddleware());
        }

        $routes = require __DIR__ . '/../routes/web.php';
        $routes($app);

        $this->app = $app;
    }

    protected function setUpTraits()
    {
        $traits = array_flip(class_uses_recursive(static::class));

        if (isset($traits[UseDatabaseTrait::class])) {
            $this->rollbackMigrations();
        }

        if (isset($traits[UseDatabaseTrait::class])) {
            $this->runMigrations();
        }
    }

    protected function tearDown()
    {
        $this->app = null;

        if (class_exists(Carbon::class)) {
            Carbon::setTestNow();
        }

        parent::tearDown();
    }

    public function request(string $request_method, string $request_uri = null, $request_data = null, array $headers = [])
    {
        $environment = Environment::mock(
            array_merge(
                [
                    'REQUEST_METHOD' => $request_method,
                    'REQUEST_URI' => $request_uri,
                    'Content-Type' => 'application/json'
                ],
                $headers
            )
        );

        $request = Request::createFromEnvironment($environment);

        if (isset($request_data)) {
            $request = $request->withParsedBody($request_data);
        }

        return $this->app->process($request, new Response());
    }

    public function get(string $request_uri, array $headers = [])
    {
        return $this->request('GET', $request_uri, null, $headers);
    }

    public function post(string $request_uri, $request_data = null, array $headers = [])
    {
        return $this->request('POST', $request_uri, $request_data, $headers);
    }

    public function put(string $request_uri, $request_data = null, array $headers = [])
    {
        return $this->request('PUT', $request_uri, $request_data, $headers);
    }

    public function patch(string $request_uri, $request_data = null, array $headers = [])
    {
        return $this->request('PATCH', $request_uri, $request_data, $headers);
    }

    public function delete(string $request_uri, $request_data = null, array $headers = [])
    {
        return $this->request('DELETE', $request_uri, $request_data, $headers);
    }

    protected function withoutMiddleware()
    {
        return [
            //
        ];
    }
}
