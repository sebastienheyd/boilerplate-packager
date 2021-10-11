<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Contracts\Filesystem\FileNotFoundException;

class RequirePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:require {package} {--dev : Put package in require-dev section}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int|void
     *
     * @throws FileNotFoundException
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

        // Clone
        $this->info('Source URL is '.$url);
        $this->info('Cloning repository...');
        $tempPath = packages_path(self::$temp.DIRECTORY_SEPARATOR.$package->vendor.DIRECTORY_SEPARATOR.$package->name);
        run_process(['git', 'clone', '-q', $url, $tempPath]);

        // Get information from composer.json
        if (! $this->storage->exists(self::$temp.DIRECTORY_SEPARATOR.$package->vendor.DIRECTORY_SEPARATOR.$package->name.DIRECTORY_SEPARATOR.'composer.json')) {
            $this->error('Package has no composer.json file!');
            $this->storage->deleteDirectory(self::$temp);

            return 1;
        }

        $composer = json_decode($this->storage->get(self::$temp.DIRECTORY_SEPARATOR.$package->vendor.DIRECTORY_SEPARATOR.$package->name.DIRECTORY_SEPARATOR.'composer.json'));
        [$vendor, $name] = explode('/', $composer->name);

        // Move to packages folder with the correct name
        $this->info("Installing package $vendor/$name...");

        if ($this->storage->exists($vendor.DIRECTORY_SEPARATOR.$name)) {
            if (! $this->confirm('<fg=yellow>The package already exists in local folder, require current local package?</>')) {
                if (! $this->confirm('<fg=yellow>Clear local package and install the downloaded one?</>')) {
                    $this->storage->deleteDirectory(self::$temp);

                    return 0;
                }

                $this->storage->deleteDirectory($vendor.DIRECTORY_SEPARATOR.$name);
                $this->storage->move(self::$temp.DIRECTORY_SEPARATOR.$package->vendor.DIRECTORY_SEPARATOR.$package->name, $vendor.DIRECTORY_SEPARATOR.$name);
            }
        } else {
            $this->storage->move(self::$temp.DIRECTORY_SEPARATOR.$package->vendor.DIRECTORY_SEPARATOR.$package->name, $vendor.DIRECTORY_SEPARATOR.$name);
        }

        $this->storage->deleteDirectory(self::$temp);

        $this->info("Require package $vendor/$name...");
        if (! $this->composer->require("$vendor/$name:@dev", $this->option('dev'))) {
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
