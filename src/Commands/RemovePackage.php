<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Sebastienheyd\BoilerplatePackager\Composer;
use Sebastienheyd\BoilerplatePackager\FileHandler;
use Sebastienheyd\BoilerplatePackager\Packagist;

class RemovePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:remove {package?}';

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
    public function __construct(Packagist $packagist, FileHandler $fileHandler, Composer $composer)
    {
        parent::__construct();
        $this->packagist = $packagist;
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
        $package = $this->argument('package');

        if (! $package) {
            $choices = [];
            $path = $this->fileHandler->packagesDir();
            foreach (array_diff(scandir($path), ['.', '..']) as $vendor) {
                if (! is_dir("$path/$vendor")) {
                    continue;
                }

                foreach (array_diff(scandir("$path/$vendor"), ['.', '..']) as $name) {
                    $choices[] = "$vendor/$name";
                }
            }
            $package = $this->choice('Which package do you want to remove?', $choices);
        }

        // If is format vendor/name get information from packagist
        if (! $this->packagist->checkFormat($package)) {
            $this->error('The package format must be vendor/name');

            return 1;
        }

        if (! $this->composer->isInstalled($package)) {
            $this->error("The package $package is not installed!");

            return 1;
        }

        if (! is_dir($this->fileHandler->packagesDir($package))) {
            $this->error("The package $package is not a local package, you have to remove it manually!");

            return 1;
        }

        if (! $this->confirm("<bg=red>You are about to remove the package $package, are you sure?</>")) {
            return 0;
        }

        $this->info("Removing package $package from composer...");
        if (! $this->composer->remove($package)) {
            $this->error('Fail to remove from composer!');

            return 1;
        }

        if (is_link(base_path("vendor/$package"))) {
            $this->info("Removing symlink vendor/$package...");
            unlink(base_path("vendor/$package"));
        }

        if ($this->confirm("<fg=yellow>Removing folder packages/$package?</>")) {
            $this->fileHandler->removeDir($this->fileHandler->packagesDir($package));
        }

        $this->info('Package removed successfully!');
    }
}
