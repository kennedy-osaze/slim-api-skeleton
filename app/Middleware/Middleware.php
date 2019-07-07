<?php

namespace App\Middleware;

use App\Traits\ContainerTrait;
use Psr\Container\ContainerInterface;

class Middleware
{
    use ContainerTrait;

    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
