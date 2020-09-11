<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Sebastienheyd\BoilerplatePackager\Composer;
use Sebastienheyd\BoilerplatePackager\FileHandler;
use Sebastienheyd\BoilerplatePackager\Packagist;
use Sebastienheyd\BoilerplatePackager\Skeleton;

class CreatePackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:create {package} {--dev}';

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
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var Skeleton
     */
    protected $skeleton;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Packagist $packagist, FileHandler $fileHandler, Composer $composer, Skeleton $skeleton)
    {
        parent::__construct();
        $this->packagist = $packagist;
        $this->fileHandler = $fileHandler;
        $this->composer = $composer;
        $this->skeleton = $skeleton;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $package = Str::lower($this->argument('package'));
        $this->alert("Creating a new package $package");

        if (! $this->packagist->checkFormat($package)) {
            $this->error('Package name format must be vendor/package');
            fwrite(STDERR, 'Package name format must be vendor/package');
            return 1;
        }

        $this->getOutput()->write('<fg=green>Checking if package already exists on packagist... </>');
        if ($this->packagist->exists($package)) {
            $this->getOutput()->write(PHP_EOL);
            if ($this->confirm('Package already exists on packagist, do you want to install it?')) {
                return $this->call('boilerplate:packager', ['action' => 'require', 'package' => $package]);
            }

            return 0;
        } else {
            $this->getOutput()->write('<fg=green>ok</>');
        }

        $this->getOutput()->write(PHP_EOL);

        [$vendor, $package] = explode('/', $package);

        $this->skeleton->assign([
            'author_name' => $this->forceAnswer('Author name', config('boilerplate.packager.author_name')),
            'author_email' => $this->forceAnswer('Author e-mail', config('boilerplate.packager.author_email')),
            'package_description' => $this->forceAnswer('Package description'),
            'license' => $this->forceAnswer('License', config('boilerplate.packager.license')),
            'vendor' => $vendor,
            'package' => $package,
            'date' => date('Y_m_d_His'),
            'locale' => config('boilerplate.app.locale'),
        ]);

        $resource = Str::singular($this->forceAnswer('Resource name'));
        $this->skeleton->assign([
            'resource' => strtolower($resource),
        ]);

        $this->info('Download package skeleton...');
        $this->skeleton->download(config('boilerplate.packager.skeleton'), config('boilerplate.packager.skeleton_branch'));

        $this->info('Building package...');
        $this->skeleton->build();

        $src = $this->fileHandler->tempDir();
        $dest = $this->fileHandler->packagesDir("$vendor/$package");
        if (is_dir($dest)) {
            if ($this->confirm("<fg=yellow>Package $vendor/$package is already installed, replace package?</>")) {
                $this->fileHandler->removeDir($dest);
            } else {
                $this->fileHandler->removeDir($src);

                return 0;
            }
        }

        $this->fileHandler->moveDir($src, $dest);

        $this->info("Require package $vendor/$package...");
        $this->composer->require("$vendor/$package:@dev", $this->option('dev'));

        if (! is_link(base_path("vendor/$vendor/$package"))) {
            $this->error('Package installed is not the local version!');
            fwrite(STDERR, 'Package installed is not the local version!');
            return 1;
        }

        $this->info('Package successfully created!');
    }

    private function forceAnswer($question, $default = null)
    {
        if (empty($default)) {
            $default = null;
        }

        $result = $this->ask($question, $default);

        if (! $result) {
            $this->error('Answer cannot be empty');

            return $this->forceAnswer($question, $default);
        }

        return $result;
    }
}
