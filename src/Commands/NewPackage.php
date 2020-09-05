<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Sebastienheyd\BoilerplatePackager\Composer;
use Sebastienheyd\BoilerplatePackager\FileHandler;
use Sebastienheyd\BoilerplatePackager\Packagist;
use Sebastienheyd\BoilerplatePackager\Skeleton;

class NewPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:new {package}';

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

        if (!$this->packagist->checkFormat($package)) {
            $this->error('Package name format must be vendor/package');
            exit;
        }

        if ($this->packagist->exists($package)) {
            if ($this->confirm('Package already exists on packagist, do you want to install it?')) {
                $this->getApplication()->addCommands([$this->resolveCommand(__NAMESPACE__.'\\InstallPackage')]);
                $this->call('boilerplate:packager:install', ['package' => $package]);
            }
            exit;
        }

        [$vendor, $package] = explode('/', $package);
        $this->alert("Creating a new package $vendor/$package");

        $this->skeleton->assign([
            'author_name'         => $this->forceAnswer('Author name'),
            'author_email'        => $this->forceAnswer('Author email'),
            'package_description' => $this->forceAnswer('Package description'),
            'license'             => $this->forceAnswer('License', 'MIT'),
            'uc:vendor'           => Str::studly($vendor),
            'uc:package'          => Str::studly($package),
            'sc:vendor'           => Str::slug($vendor, '_'),
            'sc:package'          => Str::slug($package, '_'),
            'vendor'              => $vendor,
            'package'             => $package,
            'date'                => date('Y_m_d_His'),
            'locale'              => config('boilerplate.app.locale'),
        ]);

        $this->info('Download package skeleton...');
        $this->skeleton->download();

        $this->info('Building package...');
        $this->skeleton->build();

        //$this->fileHandler->removeDir($this->fileHandler->tempDir());

        $this->info('Package successfully created!');
    }

    private function forceAnswer($question, $default = null)
    {
        $result = $this->ask($question, $default);

        if (!$result) {
            $this->error('Answer cannot be empty');
            return $this->forceAnswer($question, $default);
        }

        return $result;
    }
}
