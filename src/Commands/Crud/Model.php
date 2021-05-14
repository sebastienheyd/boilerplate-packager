<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Str;

class Model extends Command
{
    protected $signature = 'boilerplate:packager:crud:model {package} {table}';
    protected $description = '';

    public function handle()
    {
        $table = $this->argument('table');

        $package = $this->argument('package');
        $className = Str::studly(Str::singular($table));
        $path = $package.'/src/Models/'.$className.'.php';

        if($this->storage->exists($path)) {
//            if (! $this->confirm($className.' model already exists, overwrite?')) {
//                return 0;
//            }
        }

        $columns = $this->getColumnsFromTable($table);

        $model = (string) view('packager::model', [
            'namespace'     => $this->getNamespace($package).'\Models',
            'className'     => $className,
            'table'         => $table,
            'fillable'      => $columns->filter(function ($column) {
                if(preg_match('#_id$#', $column['name'])) {
                    return false;
                }
                return !in_array($column['name'], ['id', 'created_at', 'updated_at', 'deleted_at', 'remember_token']);
            })->pluck('name')->join("','"),
            'dates'         => $columns->filter(function ($column) {
                if (in_array($column['name'], ['created_at', 'updated_at', 'deleted_at'])) {
                    return false;
                }
                return $column['type'] == "datetime" || $column['type'] == "date";
            })->pluck('name')->join("','"),
            'hidden'        => $columns->filter(function ($column) {
                return in_array($column['name'], ['password', 'remember_token']);
            })->pluck('name')->join("','"),
            'timestamps'    => $columns->filter(function ($column) {
                    return in_array($column['name'], ['created_at', 'updated_at']);
                })->count() > 0,
            'hasSoftDelete' => $columns->filter(function ($column) {
                    return $column['name'] == "deleted_at";
                })->count() > 0,
            'relations' => $this->getTableRelations($table),
        ]);

        $this->info("Writing $className model");
        $this->storage->put($path, $model);
    }
}
