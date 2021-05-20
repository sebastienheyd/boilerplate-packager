<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Support\Str;

class CrudPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:crud {package}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return int
     */
    public function handle()
    {
        $package = Str::lower($this->argument('package'));

//            if (! $this->confirm('Confirm?')) {
//                return 0;
//            }

        if (!$this->packagist->checkFormat($package)) {
            $this->error('Package name format must be vendor/package');

            return 1;
        }

        if (!$this->storage->exists($package)) {
            $this->error('Package does not exists');

            return 1;
        }

        $tables = $this->getPackageTables($package);

        if (empty($tables)) {
            $this->error('No table creation in the package');

            return 1;
        }

        $args = ['tables' => $tables, 'package' => $package];
        $this->callCommand('model', $args);
        $this->callCommand('routes', $args);
        $this->callCommand('lang', $args);
        $this->callCommand('permissions', $args);
        $this->callCommand('controller', $args);
        $this->callCommand('menu', $args);
        $this->callCommand('views', $args);
    }

    private function callCommand($action, $args)
    {
        $this->getApplication()->addCommands([$this->resolveCommand(__NAMESPACE__.'\\Crud\\'.ucfirst($action))]);
        $this->call('boilerplate:packager:crud:'.$action, $args);
    }

    private function getPackageTables($package)
    {
        $migrations = $this->storage->files($package.'/src/database/migrations');

        $tables = [];

        foreach ($migrations as $migrationFile) {
            $migration = $this->storage->get($migrationFile);

            if (!preg_match_all('#Schema::create\(\s*[\'"]([a-z]+)[\'"]#', $migration, $m)) {
                continue;
            }

            $tables = array_merge($tables, $m[1]);
        }

        return array_unique($tables);
    }
}
