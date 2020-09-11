<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Sebastienheyd\BoilerplatePackager\FileHandler;

class Packager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager
        {action? : new, install, remove, list}
        {package? : Package name in vendor/package format or repository URL (for install only)}
        {--dev : Put package in require-dev section}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage packages for sebastienheyd/boilerplate';

    /**
     * @var FileHandler
     */
    protected $fileHandler;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(FileHandler $fileHandler)
    {
        parent::__construct();

        if (! is_dir($fileHandler->packagesDir())) {
            mkdir($fileHandler->packagesDir(), 0775, true);
            file_put_contents($fileHandler->packagesDir('.gitignore'), '.temp/');
        }

        if (is_dir($fileHandler->tempDir())) {
            $fileHandler->removeDir($fileHandler->tempDir());
        }
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');

        if (! in_array($action, ['create', 'require', 'remove', 'list'])) {
            $this->help();

            return 0;
        }

        $this->getApplication()->addCommands([$this->resolveCommand(__NAMESPACE__.'\\'.ucfirst($action).'Package')]);

        $args = [];

        if ($this->argument('package')) {
            $args['package'] = $this->argument('package');
        }

        if ($this->option('dev')) {
            $args['--dev'] = true;
        }

        return $this->call('boilerplate:packager:'.$action, $args);
    }

    public function help()
    {
        $this->warn('Description');
        $this->line('  '.$this->getDescription());
        $this->line('');
        $this->warn('Usage');
        $this->line('  <fg=green>boilerplate:package list</>                                   List all local packages.');
        $this->line('  <fg=green>boilerplate:package create <package> [--dev]</>               Create a new local package.');
        $this->line('  <fg=green>boilerplate:package require <package|repository> [--dev]</>   Install an existing package.');
        $this->line('  <fg=green>boilerplate:package remove <package></>                       Remove a locally installed package.');
        $this->line('');
        $this->warn('Arguments');
        $this->line('  <fg=green>package</>     Package name in vendor/package format');
        $this->line('  <fg=green>repository</>  Repository URL (https or ssh)');
        $this->line('');
        $this->warn('Options');

        // Display all options with descriptions.
        foreach ($this->getDefinition()->getOptions() as $name => $option) {
            $shortCut = empty($option->getShortcut()) ? '' : '-'.$option->getShortcut().',';

            if (strlen($shortCut) <= 6) {
                $shortCut = str_pad($shortCut, 5, ' ', STR_PAD_LEFT);
            } else {
                $shortCut = '  '.$shortCut;
            }

            $this->line('<fg=green>'.str_pad($shortCut.' --'.$name, 25, ' ', STR_PAD_RIGHT).'</> '.$option->getDescription());
        }
    }
}
