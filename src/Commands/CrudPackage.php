<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class CrudPackage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager:crud 
        {package : package name where to scaffold} 
        {--prefix= : Table prefix to remove when generating files}
        {--only=* : Define which files you want to generate (model, routes, lang, permissions, controller, menu, views}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    protected $options = [];

    /**
     * @return int
     *
     */
    public function handle()
    {
        $package = Str::lower($this->argument('package'));
        if (! $this->packagist->checkFormat($package)) {
            $this->error('Package name format must be vendor/package');

            return 1;
        }

        if (! $this->storage->exists($package)) {
            $this->error('Package does not exists');

            return 1;
        }

        $tables = $this->getPackageTables($package);

        if (empty($tables)) {
            $this->error('No table creation in the package');

            return 1;
        }

        $namespaces = [];

        foreach ($tables as $k => $table) {
            foreach (Schema::getConnection()->getDoctrineSchemaManager()->listTableForeignKeys($table) as $fk) {
                if (in_array($fk->getForeignTableName(), $tables)) {
                    continue;
                }

                $model = Str::studly(Str::singular($fk->getForeignTableName()));
                $namespaces[$fk->getForeignTableName()] = $this->checkModel($model);
            }

            // Remove pivot tables
            if (preg_match('#^([a-z]+)_([a-z]+)$#', $table)) {
                unset($tables[$k]);
            }
        }

        $warn = '  Warning ! This command will overwrite all files in the package '.$package.'  ';
        $this->warn(str_repeat('*', strlen($warn)));
        $this->warn($warn);
        $this->warn(str_repeat('*', strlen($warn)));

        if (! $this->confirm('Confirm?')) {
            return 0;
        }

        $args = ['tables' => $tables, 'package' => $package];

        $this->options = $this->option('only');
        $args['prefix'] = $this->option('prefix');

        $this->callCommand('model', array_merge_recursive($args, ['namespaces' => $namespaces]));
        $this->callCommand('routes', $args);
        $this->callCommand('lang', $args);
        $this->callCommand('permissions', $args);
        $this->callCommand('controller', array_merge_recursive($args, ['namespaces' => $namespaces]));
        $this->callCommand('menu', $args);
        $this->callCommand('views', $args);
    }

    private function callCommand($action, $args)
    {
        if (empty($this->options) || in_array($action, $this->options)) {
            $this->getApplication()->addCommands([$this->resolveCommand(__NAMESPACE__.'\\Crud\\'.ucfirst($action))]);
            $this->call('boilerplate:packager:crud:'.$action, $args);
        }
    }

    private function getPackageTables($package)
    {
        $migrations = $this->storage->files($package.'/src/database/migrations');

        $tables = [];

        foreach ($migrations as $migrationFile) {
            $migration = $this->storage->get($migrationFile);

            if (! preg_match_all('#Schema::create\(\s*[\'"]([a-z_]+)[\'"]#', $migration, $m)) {
                continue;
            }

            $tables = array_merge($tables, $m[1]);
        }

        return array_unique($tables);
    }

    private function checkModel($model)
    {
        $msg = sprintf('Input the namespace for the model <comment>%s</comment>', $model);
        $ns = $this->ask($msg, 'App\Models');

        if (! class_exists($ns.'\\'.$model)) {
            $this->line(' <error> Class <fg=yellow;bg=red>'.$ns.'\\'.$model.'</> does not exists </error>');

            return $this->checkModel($model);
        }

        return $ns;
    }
}
