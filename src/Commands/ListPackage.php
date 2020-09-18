<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $list = [];

        foreach ($this->storage->directories() as $directory) {
            if ($directory === self::$temp) {
                $this->storage->deleteDirectory(self::$temp);
                continue;
            }

            foreach ($this->storage->directories($directory) as $package) {
                [$vendor, $name] = explode(DIRECTORY_SEPARATOR, $package);

                $list[$package] = [
                    'vendor' => $vendor,
                    'name' => $name,
                    'installed' => '-',
                    'remote_url' => '-',
                    'branch' => '-',
                    'require-dev' => '-',
                ];

                (new Process(['git', 'branch'], packages_path($package)))->run(function (
                    $type,
                    $buffer
                ) use (
                    &$list,
                    $package
                ) {
                    if (preg_match('`^\*\s(.*)$`m', $buffer, $m)) {
                        $list[$package]['branch'] = $m[1];
                    }
                });

                (new Process(['git', 'config', '--get', 'remote.origin.url'], packages_path($package)))->run(function (
                    $type,
                    $buffer
                ) use (
                    &$list,
                    $package
                ) {
                    $list[$package]['remote_url'] = trim($buffer);
                });

                if (is_link(base_path('vendor'.DIRECTORY_SEPARATOR.$package))) {
                    $linkRel = implode(DIRECTORY_SEPARATOR, ['..', '..', 'packages', $vendor, $name]);
                    if (readlink(base_path('vendor'.DIRECTORY_SEPARATOR.$package)) === $linkRel) {
                        $list[$package]['installed'] = 'yes';
                    }
                }

                if (isset($this->composer->{'require-dev'}->{"$vendor/$name"})) {
                    $list[$package]['require-dev'] = 'yes';
                }
            }
        }

        $this->table(['Vendor', 'Name', 'Used', 'Remote URL', 'Branch', 'Require-dev'], $list);
    }
}
