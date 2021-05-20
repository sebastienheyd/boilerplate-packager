<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Str;

class Menu extends Command
{
    protected $signature = 'boilerplate:packager:crud:menu {package} {tables}';
    protected $description = '';

    public function handle()
    {
        $package = $this->argument('package');
        [$vendor, $packageName] = explode('/', $package);

        $menu = (string) view('packager::menu', [
            'vendor' => $vendor,
            'packageName' => $packageName,
            'models' => $this->argument('tables'),
        ]);

        $this->storage->put($package.'/src/Menu/'.Str::studly($packageName).'Menu.php', $menu);

        $this->info('Writing menu');
    }
}
