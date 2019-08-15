<?php

namespace App\Libraries;

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory as EloquentFactory;

class Factory
{
    /** @var \Illuminate\Database\Eloquent\Factory */
    public static $eloquent_factory;

    /**
     * Prepare and get the Illuminate Factory object
     *
     * @param \Faker\Generator #faker
     * @param string $path_to_factories
     *
     * @throws \InvalidArgumentException
     *
     * @return \Illuminate\Database\Eloquent\Factory
     */
    public static function build(Faker $faker, $path_to_factories)
    {
        static::$eloquent_factory = new EloquentFactory($faker);

        static::loadFactories($path_to_factories);

        return static::$eloquent_factory;
    }

    /**
     * Loads factories files for use by Illuminate Factory
     *
     * @param string $path Path to the factories directory
     *
     * @throws \InvalidArgumentException
     *
     * @return void
     */
    protected static function loadFactories(string $path)
    {
        $factory = static::$eloquent_factory;

        foreach (static::findFilesInFactoryDirectory($path) as $file) {
            if ($file->getExtension() === 'php') {
                require $file->getRealPath();
            }
        }

        static::$eloquent_factory = $factory;
    }

    /**
     * Fetches all files in the provided factories directory
     *
     * @param string $factory_directory
     *
     * @throws \InvalidArgumentException
     *
     * @return \RecursiveIteratorIterator
     */
    protected static function findFilesInFactoryDirectory(string $factory_directory)
    {
        if (!is_dir($factory_directory)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $factory_directory));
        }

        $directory = rtrim($factory_directory, '/' . \DIRECTORY_SEPARATOR);

        $directory_iterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);

        return new \RecursiveIteratorIterator($directory_iterator, \RecursiveIteratorIterator::LEAVES_ONLY);
    }
}
