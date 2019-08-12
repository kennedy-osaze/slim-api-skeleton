<?php

namespace Tests;

use Phinx\Console\PhinxApplication;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;

trait UseDatabaseTrait
{
    protected $run_seeds = false;

    protected function runMigration()
    {
        $app = new PhinxApplication();
        $app->setAutoExit(false);
        $app->run(new StringInput('migrate -e ' . static::ENV), new NullOutput());
    }

    protected function runSeeders(array $seeders = [])
    {
        $app = new PhinxApplication();
        $app->setAutoExit(false);

        if (empty($seeders)) {
            $app->run(new StringInput('seed:run -e ' . static::ENV), new NullOutput());
            return;
        }

        foreach ($seeders as $seeder) {
            $app->run(new StringInput(sprintf("seed:run -e %s -s %s", static::ENV, $seeder)), new NullOutput());
        }
    }

    protected function rollbackMigrations()
    {
        $app = new PhinxApplication();
        $app->doRun(new StringInput(sprintf("rollback -e %s -t 0 -f"), static::ENV), new NullOutput());
    }
}
