<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Sebastienheyd\BoilerplatePackager\Composer;
use Sebastienheyd\BoilerplatePackager\FileHandler;
use Sebastienheyd\BoilerplatePackager\Package;
use Sebastienheyd\BoilerplatePackager\Packagist;

class InstallPackage extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:install {package} {--dev}';

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
            if (!$this->packagist->exists($url)) {
                $this->error('Package does not exists on packagist.org');
                exit;
            }

            $package = $this->packagist->getPackageInformation($url);
            $this->info('Package '.$package->name.' found on packagist.org, processing...');
            $url = $package->repository;
        }

        // Get information from the given repository URL
        if (!($package = $this->package->parseFromUrl($url))) {
            $this->error('Package name or repository URL is invalid');
            exit;
        }

        // Clone in temporary folder
        $this->info('Source URL is '.$url);
        $this->info('Cloning repository...');
        $tempPath = $this->fileHandler->tempDir($package->vendor.'/'.$package->name);
        exec("git clone -q $url $tempPath", $output, $exit_code);

        // Get information from composer.json
        if (!is_file($package->temp_path.'/composer.json')) {
            $this->error('Package has no composer.json file, abort !');
            $this->fileHandler->removeDir($this->fileHandler->tempDir());
            exit;
        }

        $composer = json_decode(file_get_contents($package->temp_path.'/composer.json'));
        [$vendor, $name] = explode('/', $composer->name);

        // Move to packages folder with the correct name
        $this->info("Installing package $vendor/$name...");

        if (is_dir($this->fileHandler->packagesDir("$vendor/$name"))) {
            if (!$this->confirm('The package already exists in local folder, require current local package?')) {
                if (!$this->confirm('Clear local package and install the downloaded one?')) {
                    exit;
                } else {
                    $this->fileHandler->removeDir($this->fileHandler->packagesDir("$vendor/$name"));
                    $this->fileHandler->moveDir($tempPath, $this->fileHandler->packagesDir("$vendor/$name"));
                }
            }
        } else {
            $this->fileHandler->moveDir($tempPath, $this->fileHandler->packagesDir("$vendor/$name"));
        }

        $this->fileHandler->removeDir($this->fileHandler->tempDir());

        $this->info("Require package $vendor/$name...");
        $this->composer->addPackagesPath();
        $this->composer->require("$vendor/$name:@dev", $this->option('dev'));

        if(!is_link(base_path("vendor/$vendor/$name"))) {
            $this->error('Package installed is not the local version!');
            exit;
        }

        $this->info('Package installed successfully!');
    }
}
