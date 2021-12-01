<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Views extends Command
{
    protected $signature = 'boilerplate:packager:crud:views {package} {tables} {namespaces?} {prefix?}';
    protected $description = '';

    public function handle()
    {
        foreach ($this->argument('tables') as $table) {
            $this->buildViews($table);
        }
    }

    private function buildViews($table)
    {
        $package = $this->argument('package');
        [$vendor, $packageName] = explode('/', $package);
        $columns = $this->getColumnsFromTable($table);
        $relations = $this->getTableRelations($table, $this->argument('prefix'));

        $fields = [];
        foreach ($columns as $column) {
            if (in_array($column['name'], ['created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            if (preg_match('#_id$#', $column['name'])) {
                continue;
            }

            $rules = [$column['required'] ? 'required' : 'nullable'];

            $fields[] = [
                'name' => $column['name'],
                'type' => $column['type'],
                'required' => $column['required'],
                'rules' => join('|', $rules),
            ];
        }

        $data = [
            'namespaces' => Namespaces::get(),
            'namespace' => $this->getNamespace($package),
            'resource' => preg_replace('#^'.$this->argument('prefix').'#', '', $table),
            'vendor' => $vendor,
            'packageName' => $packageName,
            'fields' => $fields,
            'relations' => $relations,
        ];

        $path = __DIR__.'/../../resources/views/resource/laravel6';
        $files = (new Filesystem())->allFiles($path);

        foreach ($files as $file) {
            $dest = str_replace($path, '', $file);
            $view = str_replace(['.blade.php', '/'], '', $dest);
            $view = ($this->isLaravelEqualOrGreaterThan7 ? 'laravel7' : 'laravel6').'.'.$view;

            $content = (string) view('packager::resource.'.$view, $data);
            $table = preg_replace('#^'.$this->argument('prefix').'#', '', $table);

            $this->info('Writing '.$table.$dest.' view');
            $this->storage->put($package.'/src/resources/views/'.Str::singular($table).$dest, html_entity_decode($content));
        }
    }
}
