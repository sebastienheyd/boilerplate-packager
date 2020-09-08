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
    protected $signature = 'boilerplate:packager:remove {package}';

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

        // If is format vendor/name get information from packagist
        if (! $this->packagist->checkFormat($package)) {
            $this->error('The package format must be vendor/name');
            exit;
        }

        if (! isset($this->composer->require->{$package}) && ! isset($this->composer->{"require-dev"}->{$package})) {
            $this->error('The package is not installed');
            exit;
        }

        if (! is_dir($this->fileHandler->packagesDir($package)) || ! is_link(base_path("vendor/$package")) || ! (readlink(base_path("vendor/$package")) === "../../packages/$package")) {
            $this->error('The installed package is not a local package, you have to remove it manually');
            exit;
        }

        if (! $this->confirm("<bg=red>You are about to remove the package $package, are you sure?</>")) {
            exit;
        }

        $this->info("Removing package $package from composer...");
        $this->composer->remove($package);

        $this->info("Removing symlink vendor/$package...");
        unlink(base_path("vendor/$package"));

        if ($this->confirm("<fg=yellow>Removing folder packages/$package?</>")) {
            $this->fileHandler->removeDir($this->fileHandler->packagesDir($package));
        }

        $this->info('Package removed successfully!');
    }
}
