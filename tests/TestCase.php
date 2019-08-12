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
    const ENV = 'testing';

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
        $config = require_once __DIR__ . '/../config/index.php';

        $this->app = new App(['settings' => $config]);

        $dependencies = require_once __DIR__ . '/../bootstrap/dependencies.php';
        $dependencies($this->app);

        $registry = require_once __DIR__ . '/../bootstrap/registry.php';
        $registry($this->app);

        $error_handlers = require_once __DIR__ . '/../bootstrap/error-handlers.php';
        $error_handlers($this->app);

        if ($this->withMiddleware) {
            $middleware = require_once __DIR__ . '/middleware.php';
            $middleware($this->app, $this->withoutMiddleware());
        }

        $routes = require_once __DIR__ . '/../routes/web.php';
        $routes($this->app);
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

    public function request($requestMethod, $requestUri, $requestData = null, $headers = [])
    {
        $environment = Environment::mock(
            array_merge(
                [
                    'REQUEST_METHOD' => $requestMethod,
                    'REQUEST_URI' => $requestUri,
                    'Content-Type' => 'application/json'
                ],
                $headers
            )
        );

        $request = Request::createFromEnvironment($environment);

        if (isset($requestData)) {
            $request = $request->withParsedBody($requestData);
        }

        return $this->app->process($request, new Response());
    }

    protected function withoutMiddleware()
    {
        return [
            //
        ];
    }
}
