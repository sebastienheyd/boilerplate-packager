<?php

namespace Sebastienheyd\BoilerplatePackager\Tests;

use Orchestra\Testbench\TestCase as TestBench;

abstract class TestCase extends TestBench
{
    use TestHelper;

    protected const TEST_APP_TEMPLATE = __DIR__.'/../testbench/template';

    protected const TEST_APP = __DIR__.'/../testbench/laravel';

    public static function setUpBeforeClass(): void
    {
        self::removeTestbench();
        self::setUpLocalTestbench();
        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass(): void
    {
        self::removeTestbench();
        parent::tearDownAfterClass();
    }

    protected function getBasePath()
    {
        return self::TEST_APP;
    }

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
