<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $package = $this->argument('package');

        if (! $package) {
            $choices = [];
            foreach ($this->storage->directories() as $vendor) {
                foreach ($this->storage->directories($vendor) as $package) {
                    $choices[] = $package;
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

        if (! $this->storage->exists($package)) {
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
            $this->storage->deleteDirectory($package);
        }

        $this->info('Package removed successfully!');
    }
}
