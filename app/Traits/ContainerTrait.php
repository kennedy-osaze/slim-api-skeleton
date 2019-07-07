<?php

namespace App\Traits;

use RuntimeException;

trait ContainerTrait {

    /**
     * The container instance.
     *
     * @var \Psr\Container\ContainerInterface
     */
    protected $container;

    /**
     * Get container's dependency directly
     *
     * @throws \RuntimeException
     * @return mixed
     */
    public function __get($property)
    {
        if (!$this->container->has($property)) {
            throw new RuntimeException("The dependency \"{$property}\" does not exist.");
        }

        return $this->container->{$property};
    }
}
