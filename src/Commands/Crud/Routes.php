<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

class Routes extends Command
{
    protected $signature = 'boilerplate:packager:crud:routes {package} {tables}';
    protected $description = '';

    public function handle()
    {
        $package = $this->argument('package');
        [$vendor, $packageName] = explode('/', $package);

        $relations = [];
        foreach ($this->argument('tables') as $table) {
            $relations[$table] = $this->getTableRelations($table);
        }

//        dd($relations);

        $routes = (string) view('packager::routes', [
            'models' => $this->argument('tables'),
            'namespace' => $this->getNamespace($this->argument('package')),
            'vendor' => $vendor,
            'package' => $packageName,
            'relations' => $relations,
        ]);

        $this->info('Writing routes');
        $this->storage->put($package.'/src/routes/'.$packageName.'.php', $routes);
    }
}
