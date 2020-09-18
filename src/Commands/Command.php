<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command as BaseCommand;
use Illuminate\Support\Facades\Storage;
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
     * @var \Illuminate\Support\Facades\Storage
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
}
