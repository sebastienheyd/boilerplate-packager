<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Str;

class Controller extends Command
{
    protected $signature = 'boilerplate:packager:crud:controller {package} {tables} {namespaces?} {prefix?}';
    protected $description = '';

    public function handle()
    {
        foreach ($this->argument('tables') as $table) {
            $this->buildController($table);
        }
    }

    private function buildController($table)
    {
        $package = $this->argument('package');
        [$vendor, $packageName] = explode('/', $package);
        $columns = $this->getColumnsFromTable($table);
        $relations = $this->getTableRelations($table, $this->argument('prefix'));

        $fillable = [];

        foreach ($columns as $column) {
            if (in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $rules = [$column['required'] && $column['type'] !== 'boolean' ? 'required' : 'nullable'];

            $fillable[] = [
                'type' => $column['type'],
                'name' => $column['name'],
                'rules' => join('|', $rules),
            ];
        }

        $data = [
            'namespace' => $this->getNamespace($package),
            'namespaces' => $this->argument('namespaces'),
            'resource' => preg_replace('#^'.$this->argument('prefix').'#', '', $table),
            'vendor' => $vendor,
            'packageName' => $packageName,
            'fillable' => $fillable,
            'relations' => $relations,
        ];

        $model = (string) view('packager::controller', $data);

        $className = Str::studly(Str::singular(preg_replace('#^'.$this->argument('prefix').'#', '', $table)));

        $this->info("Writing $className controller");
        $this->storage->put($package.'/src/Controllers/'.$className.'Controller.php', $model);
    }
}
