<?php

namespace Sebastienheyd\BoilerplatePackager\Tests;

use Orchestra\Testbench\TestCase as TestBench;

abstract class TestCase extends TestBench
{
    use TestHelper;

    /**
     * Tell Testbench to use this package.
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['Sebastienheyd\BoilerplatePackager\ServiceProvider'];
    }
}
