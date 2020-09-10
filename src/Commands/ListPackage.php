<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Sebastienheyd\BoilerplatePackager\Composer;
use Sebastienheyd\BoilerplatePackager\FileHandler;
use Sebastienheyd\BoilerplatePackager\Packagist;
use Symfony\Component\Process\Process;

class ListPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:list';

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
        $path = $this->fileHandler->packagesDir();

        $list = [];

        foreach (array_diff(scandir($path), ['.', '..']) as $vendor) {
            if (! is_dir("$path/$vendor")) {
                continue;
            }

            foreach (array_diff(scandir("$path/$vendor"), ['.', '..']) as $name) {
                if ($vendor === '.temp') {
                    $this->fileHandler->removeDir($this->fileHandler->tempDir());
                    continue;
                }

                $list["$vendor/$name"] = [
                    'vendor' => $vendor,
                    'name' => $name,
                    'installed' => '-',
                    'remote_url' => '-',
                    'branch' => '-',
                    'require-dev' => '-',
                ];

                (new Process(['git', 'branch'], "$path/$vendor/$name"))->run(function (
                    $type,
                    $buffer
                ) use (
                    &$list,
                    $vendor,
                    $name
                ) {
                    if (preg_match('`^\*\s(.*)$`m', $buffer, $m)) {
                        $list["$vendor/$name"]['branch'] = $m[1];
                    }
                });

                (new Process(['git', 'config', '--get', 'remote.origin.url'], "$path/$vendor/$name"))->run(function (
                    $type,
                    $buffer
                ) use (
                    &$list,
                    $vendor,
                    $name
                ) {
                    $list["$vendor/$name"]['remote_url'] = trim($buffer);
                });

                if (is_link(base_path("vendor/$vendor/$name"))) {
                    if (readlink(base_path("vendor/$vendor/$name")) === "../../packages/$vendor/$name") {
                        $list["$vendor/$name"]['installed'] = 'yes';
                    }
                }

                if (isset($this->composer->{"require-dev"}->{"$vendor/$name"})) {
                    $list["$vendor/$name"]['require-dev'] = 'yes';
                }
            }
        }

        $this->table(['Vendor', 'Name', 'Used', 'Remote URL', 'Branch', 'Require-dev'], $list);
    }
}
