<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command as BaseCommand;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Sebastienheyd\BoilerplatePackager\Composer;
use Sebastienheyd\BoilerplatePackager\Package;
use Sebastienheyd\BoilerplatePackager\Packagist;
use Sebastienheyd\BoilerplatePackager\Skeleton;

class Command extends BaseCommand
{
    /**
     * @var Composer
     */
    protected $composer;

    /**
     * @var Package
     */
    protected $package;

    /**
     * @var Packagist
     */
    protected $packagist;

    /**
     * @var Skeleton
     */
    protected $skeleton;

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * Temporary folder name.
     *
     * @var string
     */
    protected static $temp = '.temp';

    public function __construct(Packagist $packagist, Composer $composer, Skeleton $skeleton, Package $package)
    {
        parent::__construct();
        $this->package = $package;
        $this->packagist = $packagist;
        $this->composer = $composer;
        $this->skeleton = $skeleton;
        $this->storage = Storage::disk('packages');
    }

    public function getPackage()
    {
        $package = Str::lower($this->argument('package'));

        if (! $package) {
            $choices = [];
            foreach ($this->storage->directories() as $vendor) {
                foreach ($this->storage->directories($vendor) as $package) {
                    $choices[] = $package;
                }
            }
            $package = $this->choice('Select a package', $choices);
        }

        return $package;
    }

    public function getSignature()
    {
        return $this->signature;
    }
}
