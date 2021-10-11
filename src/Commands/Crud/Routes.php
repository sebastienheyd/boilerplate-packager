<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

class Routes extends Command
{
    protected $signature = 'boilerplate:packager:crud:routes {package} {tables} {prefix?}';
    protected $description = '';

    public function handle()
    {
        $package = $this->argument('package');
        [$vendor, $packageName] = explode('/', $package);

        $relations = [];
        foreach ($this->argument('tables') as $table) {
            $tableNoPrefix = preg_replace('#^'.$this->argument('prefix').'#', '', $table);
            $relations[$tableNoPrefix] = $this->getTableRelations($table, $this->argument('prefix'));
        }

        $tables = collect($this->argument('tables'))->map(function ($el) {
            return preg_replace('#^'.$this->argument('prefix').'#', '', $el);
        });

        $routes = (string) view('packager::routes', [
            'models' => $tables,
            'namespace' => $this->getNamespace($this->argument('package')),
            'vendor' => $vendor,
            'package' => $packageName,
            'relations' => $relations,
        ]);

        $this->info("Writing $packageName routes");
        $this->storage->put($package.'/src/routes/'.$packageName.'.php', $routes);
    }
}
