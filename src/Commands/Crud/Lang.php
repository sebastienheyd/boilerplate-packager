<?php

namespace Sebastienheyd\BoilerplatePackager\Commands\Crud;

use Illuminate\Filesystem\Filesystem;

class Lang extends Command
{
    protected $signature = 'boilerplate:packager:crud:lang {package} {tables} {prefix?}';
    protected $description = '';

    public function handle()
    {
        $package = $this->argument('package');
        $tables = $this->argument('tables');

        $path = __DIR__.'/../../resources/views/lang';
        $files = (new Filesystem())->allFiles($path);

        $fields = [];
        $relations = [];
        $resources = [];

        foreach ($tables as $table) {
            $resource = preg_replace('#^'.$this->argument('prefix').'#', '', $table);
            $resources[] = $resource;
            $fields[$resource] = $this->getColumnsFromTable($table)->pluck('name');
            $relations[$resource] = $this->getTableRelations($table, $this->argument('prefix'));
        }

        $locale = app()->getLocale();

        foreach ($files as $file) {
            app()->setLocale($file->getRelativePath());

            $dest = str_replace([$path, '.blade'], '', $file);
            $view = str_replace(['.php', '/'], ['', '.'], $dest);

            $content = (string) view('packager::lang'.$view, compact('resources', 'fields', 'relations'));

            $this->storage->put($package.'/src/resources/lang'.$dest, $content);
        }

        app()->setLocale($locale);

        $this->info('Writing locales');
    }
}
