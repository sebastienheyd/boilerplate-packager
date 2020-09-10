<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Sebastienheyd\BoilerplatePackager\Composer;
use Sebastienheyd\BoilerplatePackager\FileHandler;
use Sebastienheyd\BoilerplatePackager\Package;
use Sebastienheyd\BoilerplatePackager\Packagist;

class RequirePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:require {package} {--dev}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * @var Packagist
     */
    protected $packagist;

    /**
     * @var Package
     */
    protected $package;

    /**
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Packagist $packagist, Package $package, FileHandler $fileHandler, Composer $composer)
    {
        parent::__construct();
        $this->packagist = $packagist;
        $this->package = $package;
        $this->fileHandler = $fileHandler;
        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $url = $this->argument('package');

        // If is format vendor/name get information from packagist
        if ($this->packagist->checkFormat($url)) {
            $this->info('Getting package information from packagist.org...');
            if (! $this->packagist->exists($url)) {
                $this->error('Package does not exists on packagist.org');

                return 1;
            }

            $package = $this->packagist->getPackageInformation($url);
            $this->info('Package '.$package->name.' found on packagist.org, processing...');
            $url = $package->repository;
        }

        // Get information from the given repository URL
        if (! ($package = $this->package->parseFromUrl($url))) {
            $this->error('Package name or repository URL is invalid!');

            return 1;
        }

        if (is_readable(base_path("vendor/$package->vendor/$package->name"))) {
            $this->error('Package is already installed in the project!');

            return 1;
        }

        if (! strpos(@get_headers($url)[0], '200')) {
            $this->error('Package URL is not readable!');

            return 1;
        }

        // Clone in temporary folder
        $this->info('Source URL is '.$url);
        $this->info('Cloning repository...');
        $tempPath = $this->fileHandler->tempDir("$package->vendor/$package->name");
        exec("git clone -q $url $tempPath", $output, $exit_code);

        // Get information from composer.json
        if (! is_file($package->temp_path.'/composer.json')) {
            $this->error('Package has no composer.json file!');
            $this->fileHandler->removeDir($this->fileHandler->tempDir());

            return 1;
        }

        $composer = json_decode(file_get_contents($package->temp_path.'/composer.json'));
        [$vendor, $name] = explode('/', $composer->name);

        // Move to packages folder with the correct name
        $this->info("Installing package $vendor/$name...");

        if (is_dir($this->fileHandler->packagesDir("$vendor/$name"))) {
            if (! $this->confirm('<fg=yellow>The package already exists in local folder, require current local package?</>')) {
                if (! $this->confirm('<fg=yellow>Clear local package and install the downloaded one?</>')) {
                    $this->fileHandler->removeDir($this->fileHandler->tempDir());

                    return 0;
                }

                $this->fileHandler->removeDir($this->fileHandler->packagesDir("$vendor/$name"));
                $this->fileHandler->moveDir($tempPath, $this->fileHandler->packagesDir("$vendor/$name"));
            }
        } else {
            $this->fileHandler->moveDir($tempPath, $this->fileHandler->packagesDir("$vendor/$name"));
        }

        $this->fileHandler->removeDir($this->fileHandler->tempDir());

        $this->info("Require package $vendor/$name...");
        if(! $this->composer->require("$vendor/$name:@dev", $this->option('dev'))) {
            $this->error('Package installation failed, require has failed!');

            return 1;
        }

        if (! is_link(base_path("vendor/$package->vendor/$package->name"))) {
            $this->error('Package installation failed, symlink has not been created!');

            return 1;
        }

        $this->info('Package installed successfully!');
    }
}
