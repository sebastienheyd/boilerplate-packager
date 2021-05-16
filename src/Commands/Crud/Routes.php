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

        $routes = (string) view('packager::routes', [
            'models' => $this->argument('tables'),
            'namespace' => $this->getNamespace($this->argument('package')),
            'vendor' => $vendor,
            'package' => $packageName
        ]);

        $this->info("Writing routes");
        $this->storage->put($package.'/src/routes/'.$packageName.'.php', $routes);
    }
}
