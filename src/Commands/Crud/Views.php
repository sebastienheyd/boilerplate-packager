<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class Views extends Command
{
    protected $signature = 'boilerplate:packager:crud:views {package} {tables}';
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
        $relations = $this->getTableRelations($table);

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
                'rules' => join('|', $rules),
            ];
        }

        $data = [
            'namespace' => $this->getNamespace($package),
            'resource' => $table,
            'vendor' => $vendor,
            'packageName' => $packageName,
            'fields' => $fields,
            'relations' => $relations,
        ];

        $path = __DIR__.'/../../resources/views/resource';
        $files = (new Filesystem())->allFiles($path);

        foreach ($files as $file) {
            $dest = str_replace($path, '', $file);
            $view = str_replace(['.blade.php', '/'], '', $dest);

            $content = (string) view('packager::resource.'.$view, $data);
            $this->storage->put($package.'/src/resources/views/'.Str::singular($table).$dest, $content);
        }
    }
}
