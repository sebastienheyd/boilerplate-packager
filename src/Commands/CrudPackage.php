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

        foreach ($tables as $table) {
            $this->callCommand('model', $table, $package);
        }
    }

    private function callCommand($action, $table, $package)
    {
        $this->getApplication()->addCommands([$this->resolveCommand(__NAMESPACE__.'\\Crud\\'.ucfirst($action))]);
        $this->call('boilerplate:packager:crud:'.$action, ['table' => $table, 'package' => $package]);
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
