<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Str;

class Menu extends Command
{
    protected $signature = 'boilerplate:packager:crud:menu {package} {tables} {prefix?}';
    protected $description = '';

    public function handle()
    {
        $package = $this->argument('package');
        [$vendor, $packageName] = explode('/', $package);

        $tables = collect($this->argument('tables'))->map(function ($el) {
            return preg_replace('#^'.$this->argument('prefix').'#', '', $el);
        });

        $menu = (string) view('packager::menu', [
            'vendor' => $vendor,
            'packageName' => $packageName,
            'models' => $tables,
        ]);

        $this->storage->put($package.'/src/Menu/'.Str::studly($packageName).'Menu.php', $menu);

        $this->info("Writing $packageName menu");
    }
}
