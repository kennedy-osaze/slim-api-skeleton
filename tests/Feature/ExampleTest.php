<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function testBasicTest()
    {
        $response = $this->get('/v1');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
