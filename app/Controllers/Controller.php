<?php

namespace App\Controllers;

use App\Traits\ContainerTrait;
use Psr\Container\ContainerInterface;

class Controller
{
    use ContainerTrait;

    /**
     * The container instance.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Set up controllers to have access to the container.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
