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
        if (! file_exists(self::TEST_APP_TEMPLATE)) {
            self::setUpLocalTestbench();
        }
        parent::setUpBeforeClass();
    }

    /**
     * Setup before each test.
     */
    public function setUp(): void
    {
        $this->installTestApp();
        parent::setUp();
    }

    /**
     * Tear down after each test.
     */
    public function tearDown(): void
    {
        $this->uninstallTestApp();
        parent::tearDown();
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
    protected function getPackageProviders()
    {
        return [Sebastienheyd\BoilerplatePackager\ServiceProvider::class];
    }
}
