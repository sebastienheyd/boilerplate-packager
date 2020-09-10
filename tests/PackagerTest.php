<?php

namespace Sebastienheyd\BoilerplatePackager\Tests;
use Illuminate\Support\Facades\Artisan;

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
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'sebastienheyd/boilerplate'])
            ->expectsConfirmation('Package already exists on packagist, do you want to install it?', 'no')
            ->assertExitCode(0);
    }

    public function testCreateExistingPackage()
    {
        $this->artisan('boilerplate:packager', ['action' => 'create', 'package' => 'sebastienheyd/boilerplate'])
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

    public function testRequireBadPackageNameOrUrl()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'bad'])
            ->expectsOutput('Package name or repository URL is invalid!')
            ->assertExitCode(1);
    }

    public function testRequirePackageAlreadyExistsInProject()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'sebastienheyd/boilerplate'])
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
    }

    public function testRequirePackageAlreadyExistsLocally()
    {
        unlink(self::TEST_APP.'/vendor/sebastienheyd/boilerplate');

        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'sebastienheyd/boilerplate'])
            ->expectsConfirmation('<fg=yellow>The package already exists in local folder, require current local package?</>', 'yes')
            ->expectsOutput('Package installed successfully!')
            ->assertExitCode(0);
    }

    public function testRequirePackageAlreadyExistsLocallyAndNoReplace()
    {
        unlink(self::TEST_APP.'/vendor/sebastienheyd/boilerplate');

        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'sebastienheyd/boilerplate'])
            ->expectsConfirmation('<fg=yellow>The package already exists in local folder, require current local package?</>', 'no')
            ->expectsConfirmation('<fg=yellow>Clear local package and install the downloaded one?</>', 'no')
            ->assertExitCode(0);
    }

    public function testRequirePackageAlreadyExistsLocallyAndReplace()
    {
        $this->artisan('boilerplate:packager', ['action' => 'require', 'package' => 'sebastienheyd/boilerplate'])
            ->expectsConfirmation('<fg=yellow>The package already exists in local folder, require current local package?</>', 'no')
            ->expectsConfirmation('<fg=yellow>Clear local package and install the downloaded one?</>', 'yes')
            ->expectsOutput('Package installed successfully!')
            ->assertExitCode(0);
    }

    public function testPackagerList()
    {
        $this->artisan('boilerplate:packager', ['action' => 'list'])
            ->expectsTable(
                ['Vendor', 'Name', 'Used', 'Remote URL', 'Branch', 'Require-dev'],
                [
                    ['my-vendor', 'my-package', 'yes', '-', '-', '-'],
                    ['sebastienheyd', 'boilerplate', 'yes', 'https://github.com/sebastienheyd/boilerplate', 'master', '-'],
                ]
            )
            ->assertExitCode(0);
    }
}
