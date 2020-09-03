<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Sebastienheyd\BoilerplatePackager\Package;

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
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Package $package)
    {
        parent::__construct();
        $this->package = $package;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if (!$this->package->checkFormat($this->argument('package'))) {
            $this->error('Package name format must be vendor/package');
            exit;
        }

        if ($this->package->existsOnPackagist($this->argument('package'))) {
            $this->error('Package already exist on packagist');
            exit;
        }


    }
}
