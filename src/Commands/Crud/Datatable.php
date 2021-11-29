<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Support\Str;

class Datatable extends Command
{
    protected $signature = 'boilerplate:packager:crud:datatable {package} {tables} {namespaces?} {prefix?}';
    protected $description = '';

    public function handle()
    {
        foreach ($this->argument('tables') as $table) {
            $this->buildDatatables($table);
        }
    }

    private function buildDatatables($table)
    {
        $package = $this->argument('package');
        [$vendor, $packageName] = explode('/', $package);
        $className = Str::studly(Str::singular(preg_replace('#^'.$this->argument('prefix').'#', '', $table)));
        $columns = $this->getColumnsFromTable($table);

        $datatable = (string) view('packager::datatable', [
            'namespace'     => $this->getNamespace($package),
            'resource'      => preg_replace('#^'.$this->argument('prefix').'#', '', $table),
            'className'     => $className,
            'vendor'        => $vendor,
            'packageName'   => $packageName,
            'table'         => $table,
            'columns'       => $columns,
            'relations' => $this->getTableRelations($table, $this->argument('prefix')),
        ]);

        $this->info("Writing $className datatable");
        $this->storage->put($package.'/src/Datatables/'.Str::pluralStudly($className).'Datatable.php', $datatable);
    }
}
