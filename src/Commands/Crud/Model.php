<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Str;

class Model extends Command
{
    protected $signature = 'boilerplate:packager:crud:model {package} {tables} {namespaces?} {prefix?}';
    protected $description = '';

    public function handle()
    {
        foreach ($this->argument('tables') as $table) {
            $this->buildModel($table);
        }
    }

    private function buildModel($table)
    {
        $package = $this->argument('package');
        $className = Str::studly(Str::singular(preg_replace('#^'.$this->argument('prefix').'#', '', $table)));
        $columns = $this->getColumnsFromTable($table);

        $model = (string) view('packager::model', [
            'namespace'     => $this->getNamespace($package).'\Models',
            'namespaces'    => $this->argument('namespaces'),
            'className'     => $className,
            'table'         => $table,
            'fillable'      => $columns->filter(function ($column) {
                return ! in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token']);
            })->pluck('name')->join("','"),
            'dates'         => $columns->filter(function ($column) {
                if (in_array($column['name'], ['created_at', 'updated_at', 'deleted_at'])) {
                    return false;
                }

                return $column['type'] == 'datetime' || $column['type'] == 'date';
            })->pluck('name')->join("','"),
            'hidden'        => $columns->filter(function ($column) {
                return in_array($column['name'], ['password', 'remember_token']);
            })->pluck('name')->join("','"),
            'timestamps'    => $columns->filter(function ($column) {
                return in_array($column['name'], ['created_at', 'updated_at']);
            })->count() > 0,
            'hasSoftDelete' => $columns->filter(function ($column) {
                return $column['name'] == 'deleted_at';
            })->count() > 0,
            'relations' => $this->getTableRelations($table, $this->argument('prefix')),
        ]);

        $this->info("Writing $className model");
        $this->storage->put($package.'/src/Models/'.$className.'.php', $model);
    }
}
