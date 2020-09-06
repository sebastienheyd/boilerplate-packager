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
        $this->alert("Creating a new package $package");

        if (!$this->packagist->checkFormat($package)) {
            $this->error('Package name format must be vendor/package');
            exit;
        }

        $this->getOutput()->write('<fg=green>Checking if package already exists on packagist... </>');
        if ($this->packagist->exists($package)) {
            $this->getOutput()->write(PHP_EOL);
            if ($this->confirm('Package already exists on packagist, do you want to install it?')) {
                $this->getApplication()->addCommands([$this->resolveCommand(__NAMESPACE__.'\\InstallPackage')]);
                $this->call('boilerplate:packager:install', ['package' => $package]);
            }
            exit;
        } else {
            $this->getOutput()->write('<fg=green>ok</>');
        }

        $this->getOutput()->write(PHP_EOL);

        [$vendor, $package] = explode('/', $package);

        $this->skeleton->assign([
            'author_name'         => $this->forceAnswer('Author name'),
            'author_email'        => $this->forceAnswer('Author email'),
            'package_description' => $this->forceAnswer('Package description'),
            'license'             => $this->forceAnswer('License', 'MIT'),
            'uc:vendor'           => Str::studly($vendor),
            'uc:package'          => Str::studly($package),
            'sc:vendor'           => Str::slug($vendor, '_'),
            'sc:package'          => Str::slug($package, '_'),
            'wd:package'          => mb_convert_case(Str::slug($package, ' '), MB_CASE_TITLE),
            'wd:package'          => mb_convert_case(Str::slug($package, ' '), MB_CASE_TITLE),
            'vendor'              => $vendor,
            'package'             => $package,
            'date'                => date('Y_m_d_His'),
            'locale'              => config('boilerplate.app.locale'),
        ]);

        $resource = Str::singular($this->forceAnswer('Resource name'));
        $this->skeleton->assign([
            'uc:pl:resource' => Str::plural(mb_convert_case(Str::slug($resource, ' '), MB_CASE_TITLE)),
            'pl:resource' => Str::plural(Str::slug($resource, ' '), MB_CASE_TITLE),
            'wd:resource' => mb_convert_case(Str::slug($resource, ' '), MB_CASE_TITLE),
            'uc:resource' => Str::studly($resource),
            'resource'    => strtolower($resource),
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
