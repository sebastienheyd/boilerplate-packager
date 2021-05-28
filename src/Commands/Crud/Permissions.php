<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Str;

class Permissions extends Command
{
    protected $signature = 'boilerplate:packager:crud:permissions {package} {tables}';
    protected $description = '';

    protected $i = 0;

    public function handle()
    {
        foreach ($this->argument('tables') as $k => $table) {
            $this->buildPermissionsCategory($table);
            $this->buildPermissions($table);
        }
    }

    private function buildPermissionsCategory($table)
    {
        $package = $this->argument('package');
        $resource = Str::singular($table);

        $str = '%s/src/database/migrations/%s_%s_permissions_category.php';
        $fileName = sprintf($str, $package, date('Y_m_d_Hi'.str_pad($this->i, 2, '0', STR_PAD_LEFT)), $resource);

        foreach ($this->storage->allFiles($package.'/src/database/migrations') as $file) {
            if (preg_match('#_'.$resource.'_permissions_category.php$#', $file)) {
                $pathPrefix = $this->storage->getDriver()->getAdapter()->getPathPrefix();
                $fileName = str_replace($pathPrefix, '', $file);
            }
        }

        [$vendor, $name] = explode('/', $package);
        $content = (string) view('packager::permissions_category', compact('resource', 'name'));

        $this->storage->put($fileName, $content);
        $this->i++;
    }

    private function buildPermissions($table)
    {
        $package = $this->argument('package');
        $resource = Str::singular($table);

        $str = '%s/src/database/migrations/%s_%s_permissions.php';
        $fileName = sprintf($str, $package, date('Y_m_d_Hi'.str_pad($this->i, 2, '0', STR_PAD_LEFT)), $resource);

        foreach ($this->storage->allFiles($package.'/src/database/migrations') as $file) {
            if (preg_match('#_'.$resource.'_permissions.php$#', $file)) {
                $pathPrefix = $this->storage->getDriver()->getAdapter()->getPathPrefix();
                $fileName = str_replace($pathPrefix, '', $file);
            }
        }

        [$vendor, $name] = explode('/', $package);
        $content = (string) view('packager::permissions', compact('resource', 'name'));

        $this->storage->put($fileName, $content);
        $this->i++;
    }
}
