<?php

namespace Sebastienheyd\BoilerplatePackager\Tests;

use Illuminate\Foundation\Application as Laravel;
use Illuminate\Filesystem\Filesystem;

trait TestHelper
{
    public const TESTBENCH_PATH = __DIR__.'/../vendor/orchestra/testbench-core/laravel';

    public static bool $once = false;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::initTestBench();
        self::refreshTestBench();
    }

    public static function initTestBench(): void
    {
        if (self::$once) {
            return;
        }

        $php = PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION.'.'.PHP_RELEASE_VERSION;
        echo 'Tested version : Laravel '.Laravel::VERSION.' (PHP '.$php.')'.PHP_EOL;
        echo 'SQLite version : '.\SQLite3::version()['versionString'].PHP_EOL.PHP_EOL;

        $files = new Filesystem();
        $packageComposer = json_decode($files->get(__DIR__.'/../composer.json'), true);
        $testbenchComposer = json_decode($files->get(self::TESTBENCH_PATH.'/composer.json'), true);
        $testbenchComposer['require'] = $packageComposer['require'];
        $testbenchComposer['prefer-stable'] = true;
        $files->put(self::TESTBENCH_PATH.'/composer.json', json_encode($testbenchComposer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        self::$once = true;
    }

    protected static function refreshTestBench(): void
    {
        self::deleteTestBench();
        self::backupTestBench();
    }

    protected static function backupTestBench(): void
    {
        $files = new Filesystem();
        if ($files->exists(self::TESTBENCH_PATH.'.backup')) {
            return;
        }

        $files->makeDirectory(self::TESTBENCH_PATH.'.backup', 0755, true);
        $files->copyDirectory(self::TESTBENCH_PATH, self::TESTBENCH_PATH.'.backup');
    }

    protected static function deleteTestBench(): void
    {
        $files = new Filesystem();
        if (! $files->exists(self::TESTBENCH_PATH.'.backup')) {
            return;
        }

        $files->deleteDirectory(self::TESTBENCH_PATH);
        $files->copyDirectory(self::TESTBENCH_PATH.'.backup', self::TESTBENCH_PATH);
        $files->deleteDirectory(self::TESTBENCH_PATH.'.backup');
    }
}
