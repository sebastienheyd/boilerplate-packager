<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Filesystem\Filesystem;

class Lang extends Command
{
    protected $signature = 'boilerplate:packager:crud:lang {package} {tables}';
    protected $description = '';

    public function handle()
    {
        $package = $this->argument('package');
        $resources = $this->argument('tables');

        $path = __DIR__.'/../../resources/views/lang';
        $files = (new Filesystem())->allFiles($path);

        $fields = [];
        $relations = [];
        foreach ($resources as $table) {
            $fields[$table] = $this->getColumnsFromTable($table)->pluck('name');
            $relations[$table] = $this->getTableRelations($table);
        }

        foreach ($files as $file) {
            $dest = str_replace([$path, '.blade'], '', $file);
            $view = str_replace(['.php', '/'], ['', '.'], $dest);

            $content = (string) view('packager::lang'.$view, compact('resources', 'fields', 'relations'));

            $this->storage->put($package.'/src/resources/lang'.$dest, $content);
        }

        $this->info('Writing locales');
    }
}
