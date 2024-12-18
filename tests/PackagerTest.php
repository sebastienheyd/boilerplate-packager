<?php

namespace Sebastienheyd\BoilerplatePackager\Tests;

class PackagerTest extends TestCase
{
    const TEST_PACKAGE = 'sebastienheyd/boilerplate-packager';

    public function testNoAction()
    {
        $this->artisan('boilerplate:packager')
            ->expectsOutput('  Manage packages for sebastienheyd/boilerplate')
            ->assertExitCode(0);
    }

    public function testInvalidAction()
    {
        $this->artisan('boilerplate:packager', ['action' => 'test'])
            ->expectsOutput('  Manage packages for sebastienheyd/boilerplate')
            ->assertExitCode(0);
    }

    public function testCreatePackageInvalidFormat()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'test'])
            ->expectsOutput('Package name format must be vendor/package')
            ->assertExitCode(1);
    }

    public function testCreateExistingPackageAbort()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => self::TEST_PACKAGE])
            ->expectsConfirmation('Package already exists on packagist, do you want to install it?', 'no')
            ->assertExitCode(0);
    }

    public function testCreateExistingPackage()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => self::TEST_PACKAGE])
            ->expectsConfirmation('Package already exists on packagist, do you want to install it?', 'yes')
            ->expectsOutput('Package installed successfully!')
            ->assertExitCode(0);

        $composer = $this->getComposer();
        $this->assertTrue(is_link(self::TESTBENCH_PATH.'/vendor/'.self::TEST_PACKAGE));
        $this->assertTrue(is_dir(self::TESTBENCH_PATH.'/packages/'.self::TEST_PACKAGE));
        $this->assertTrue($composer['require'][self::TEST_PACKAGE] === '@dev');
    }

    public function testCreatePackage()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'my-vendor/my-package'])
            ->expectsQuestion('Author name', '') // test force answer
            ->expectsQuestion('Author name', 'John Doe')
            ->expectsQuestion('Author e-mail', 'john.doe@domain.tld')
            ->expectsQuestion('Package description', 'My new package')
            ->expectsQuestion('License', 'mit')
            ->expectsQuestion('Resource name', 'tests') // test plural instead of singular
            ->expectsOutput('Package successfully created!')
            ->assertExitCode(0);

        $composer = $this->getComposer();
        $this->assertTrue(is_link(self::TESTBENCH_PATH.'/vendor/my-vendor/my-package'));
        $this->assertTrue(is_dir(self::TESTBENCH_PATH.'/packages/my-vendor/my-package'));
        $this->assertTrue($composer['require']['my-vendor/my-package'] === '@dev');
    }

    public function testCreatePackageAlreadyInstalledAbort()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'my-vendor/my-package'])
            ->expectsQuestion('Author name', 'John Doe')
            ->expectsQuestion('Author e-mail', 'john.doe@domain.tld')
            ->expectsQuestion('Package description', 'My new package')
            ->expectsQuestion('License', 'mit')
            ->expectsQuestion('Resource name', 'test')
            ->expectsConfirmation('<fg=yellow>Package my-vendor/my-package is already installed, replace package?</>', 'no')
            ->assertExitCode(0);
    }

    public function testCreatePackageAlreadyInstalled()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'my-vendor/my-package'])
            ->expectsQuestion('Author name', 'John Doe')
            ->expectsQuestion('Author e-mail', 'john.doe@domain.tld')
            ->expectsQuestion('Package description', 'My new package')
            ->expectsQuestion('License', 'mit')
            ->expectsQuestion('Resource name', 'test')
            ->expectsConfirmation('<fg=yellow>Package my-vendor/my-package is already installed, replace package?</>', 'yes')
            ->expectsOutput('Package successfully created!')
            ->assertExitCode(0);

        $composer = $this->getComposer();
        $this->assertTrue(is_link(self::TESTBENCH_PATH.'/vendor/my-vendor/my-package'));
        $this->assertTrue(is_dir(self::TESTBENCH_PATH.'/packages/my-vendor/my-package'));
        $this->assertTrue($composer['require']['my-vendor/my-package'] === '@dev');
    }

    public function testRequirePackageNotExists()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'my-vendor/my-package'])
            ->expectsOutput('Package does not exists on packagist.org')
            ->assertExitCode(1);
    }

    public function testRequireBadPackageNameOrUrl()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'bad'])
            ->expectsOutput('Package name or repository URL is invalid!')
            ->assertExitCode(1);
    }

    public function testRequirePackageAlreadyExistsInProject()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => self::TEST_PACKAGE])
            ->expectsOutput('Package is already installed in the project!')
            ->assertExitCode(1);
    }

    public function testRequirePackageUrlDoesNotExists()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'https://github.com/bad/bad'])
            ->expectsOutput('Package URL is not readable!')
            ->assertExitCode(1);
    }

    public function testRequirePackageNoComposerJson()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'https://github.com/sebastienheyd/docker-self-signed-proxy-companion'])
            ->expectsOutput('Package has no composer.json file!')
            ->assertExitCode(1);

        $composer = $this->getComposer();
        $this->assertTrue(! is_link(self::TESTBENCH_PATH.'/vendor/sebastienheyd/docker-self-signed-proxy-companion'));
        $this->assertTrue(! is_dir(self::TESTBENCH_PATH.'/packages/sebastienheyd/docker-self-signed-proxy-companion'));
        $this->assertTrue(! isset($composer['require']['sebastienheyd/docker-self-signed-proxy-companion']));
    }

    public function testRequirePackageAlreadyExistsLocally()
    {
        unlink(self::TESTBENCH_PATH.'/vendor/'.self::TEST_PACKAGE);

        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => self::TEST_PACKAGE])
            ->expectsConfirmation('<fg=yellow>The package already exists in local folder, require current local package?</>', 'yes')
            ->expectsOutput('Package installed successfully!')
            ->assertExitCode(0);

        $composer = $this->getComposer();
        $this->assertTrue(is_link(self::TESTBENCH_PATH.'/vendor/'.self::TEST_PACKAGE));
        $this->assertTrue(is_dir(self::TESTBENCH_PATH.'/packages/'.self::TEST_PACKAGE));
        $this->assertTrue($composer['require'][self::TEST_PACKAGE] === '@dev');
    }

    public function testRequirePackageAlreadyExistsLocallyAndNoReplace()
    {
        unlink(self::TESTBENCH_PATH.'/vendor/'.self::TEST_PACKAGE);

        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => self::TEST_PACKAGE])
            ->expectsConfirmation('<fg=yellow>The package already exists in local folder, require current local package?</>', 'no')
            ->expectsConfirmation('<fg=yellow>Clear local package and install the downloaded one?</>', 'no')
            ->assertExitCode(0);
    }

    public function testRequirePackageAlreadyExistsLocallyAndReplace()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => self::TEST_PACKAGE])
            ->expectsConfirmation('<fg=yellow>The package already exists in local folder, require current local package?</>', 'no')
            ->expectsConfirmation('<fg=yellow>Clear local package and install the downloaded one?</>', 'yes')
            ->expectsOutput('Package installed successfully!')
            ->assertExitCode(0);

        $composer = $this->getComposer();
        $this->assertTrue(is_link(self::TESTBENCH_PATH.'/vendor/'.self::TEST_PACKAGE));
        $this->assertTrue(is_dir(self::TESTBENCH_PATH.'/packages/'.self::TEST_PACKAGE));
        $this->assertTrue($composer['require'][self::TEST_PACKAGE] === '@dev');
    }

    public function testPackagerList()
    {
        $this->artisan('boilerplate:packager', ['action' => 'list'])
            ->expectsTable(
                ['Vendor', 'Name', 'Used', 'Remote URL', 'Branch', 'Require-dev'],
                [
                    ['my-vendor', 'my-package', 'yes', '-', '-', '-'],
                    ['sebastienheyd', 'boilerplate-packager', 'yes', 'https://github.com/'.self::TEST_PACKAGE, 'master', '-'],
                ]
            )
            ->assertExitCode(0);
    }

    public function testPackagerRemovePackageBadFormat()
    {
        $this->artisan('boilerplate:packager', ['action' => 'remove', 'package' => 'bad'])
            ->expectsOutput('The package format must be vendor/name')
            ->assertExitCode(1);
    }

    public function testPackagerRemovePackageNotInstalled()
    {
        $this->artisan('boilerplate:packager', ['action' => 'remove', 'package' => 'test/test'])
            ->expectsOutput('The package test/test is not installed!')
            ->assertExitCode(1);
    }

    public function testPackagerRemovePackageNotLocal()
    {
        $this->artisan('boilerplate:packager', ['action' => 'remove', 'package' => 'monolog/monolog'])
            ->expectsOutput('The package monolog/monolog is not a local package, you have to remove it manually!')
            ->assertExitCode(1);
    }

    public function testPackagerRemoveNoPackage()
    {
        $this->artisan('boilerplate:packager', ['action' => 'remove'])
            ->expectsQuestion('Which package do you want to remove?', self::TEST_PACKAGE)
            ->expectsConfirmation('<bg=red>You are about to remove the package sebastienheyd/boilerplate-packager, are you sure?</>', 'yes')
            ->expectsConfirmation('<fg=yellow>Removing folder packages/sebastienheyd/boilerplate-packager?</>', 'yes')
            ->expectsOutput('Package removed successfully!')
            ->assertExitCode(0);

        $composer = $this->getComposer();
        $this->assertTrue(! is_link(self::TESTBENCH_PATH.'/vendor/'.self::TEST_PACKAGE));
        $this->assertTrue(! is_dir(self::TESTBENCH_PATH.'/packages/'.self::TEST_PACKAGE));
        $this->assertTrue(! isset($composer['require'][self::TEST_PACKAGE]));
    }

    public function testPackagerRemovePackage()
    {
        $this->artisan('boilerplate:packager', ['action' => 'remove', 'package' => 'my-vendor/my-package'])
            ->expectsConfirmation('<bg=red>You are about to remove the package my-vendor/my-package, are you sure?</>', 'yes')
            ->expectsConfirmation('<fg=yellow>Removing folder packages/my-vendor/my-package?</>', 'yes')
            ->expectsOutput('Package removed successfully!')
            ->assertExitCode(0);

        $composer = $this->getComposer();
        $this->assertTrue(! is_link(self::TESTBENCH_PATH.'/vendor/my-vendor/my-package'));
        $this->assertTrue(! is_dir(self::TESTBENCH_PATH.'/packages/my-vendor/my-package'));
        $this->assertTrue(! isset($composer['require']['my-vendor/my-package']));
    }

    private function getComposer()
    {
        $content = file_get_contents(self::TESTBENCH_PATH.'/composer.json');

        return json_decode($content, true);
    }
}
