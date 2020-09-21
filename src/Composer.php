<?php

namespace Sebastienheyd\BoilerplatePackager;

use RuntimeException;

class Composer
{
    public function __construct()
    {
        foreach (json_decode(file_get_contents(base_path('composer.json'))) as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function isInstalled($package)
    {
        $lock = json_decode(file_get_contents(base_path('composer.lock')), true);

        foreach ($lock['packages'] as $p) {
            if ($p['name'] === $package) {
                return true;
            }
        }

        foreach ($lock['packages-dev'] as $p) {
            if ($p['name'] === $package) {
                return true;
            }
        }

        return false;
    }

    public function remove($package)
    {
        $this->checkFormat($package);

        $options = ['composer', 'remove', $package, '--no-update'];

        if (isset($this->{'require-dev'}->{$package})) {
            $options[] = '--dev';
        }

        if (run_process($options)) {
            return run_process(['composer', 'update', $package]);
        }

        return false;
    }

    private function checkFormat($package)
    {
        if (! preg_match('`^([A-Za-z0-9\-]*)/([A-Za-z0-9\-]*)(:[@a-z\-]*)?$`', $package, $m)) {
            throw new RuntimeException('Package name is not well formatted');
        }
    }

    public function require($package, $dev = false)
    {
        $this->checkFormat($package);

        if ($this->addPackagesPath()) {
            $args = ['composer', 'require', $package];

            if ($dev) {
                $args[] = '--dev';
            }

            return run_process($args);
        }

        return false;
    }

    public function addPackagesPath()
    {
        if (isset($this->repositories->local)) {
            return true;
        }

        $params = json_encode([
            'type' => 'path',
            'url' => 'packages/*/*',
            'options' => [
                'symlink' => true,
            ],
        ]);

        $command = [
            'composer',
            'config',
            'repositories.local',
            $params,
            '--file',
            'composer.json',
        ];

        return run_process($command);
    }
}
