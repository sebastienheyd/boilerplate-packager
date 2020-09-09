<?php

namespace Sebastienheyd\BoilerplatePackager\Tests;

class PackagerTest extends TestCase
{
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
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'monolog/monolog'])
            ->expectsConfirmation('Package already exists on packagist, do you want to install it?', 'no')
            ->assertExitCode(0);
    }

    public function testCreateExistingPackage()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'monolog/monolog'])
            ->expectsConfirmation('Package already exists on packagist, do you want to install it?', 'yes')
            ->expectsOutput('Package installed successfully!')
            ->assertExitCode(0);
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
    }

    public function testRequirePackageNotExists()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'my-vendor/my-package'])
            ->expectsOutput('Package does not exists on packagist.org')
            ->assertExitCode(1);
    }
}
