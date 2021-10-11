<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\Parser;
use Illuminate\Support\Facades\Storage;

class Packager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager
        {action? : new, install, remove, list, crud}
        {package? : Package name in vendor/package format or repository URL (for install only)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage packages for sebastienheyd/boilerplate';

    /**
     * @var Storage
     */
    protected $storage;

    /**
     * Temporary folder name.
     *
     * @var string
     */
    protected static $temp = '.temp';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->storage = Storage::disk('packages');
        $this->storage->put('.gitignore', self::$temp.DIRECTORY_SEPARATOR);
        $this->storage->deleteDirectory(self::$temp);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("
  ____        _ _                 _       _
 |  _ \      (_) |               | |     | |
 | |_) | ___  _| | ___ _ __ _ __ | | __ _| |_ ___
 |  _ < / _ \| | |/ _ \ '__| '_ \| |/ _` | __/ _ \
 | |_) | (_) | | |  __/ |  | |_) | | (_| | ||  __/
 |____/ \___/|_|_|\___|_|  | .__/|_|\__,_|\__\___/
                           | |
                           |_|
");

        $action = $this->argument('action');

        if (! in_array($action, ['create', 'require', 'remove', 'list', 'crud'])) {
            $this->help();

            return 0;
        }

        $command = $this->resolveCommand(__NAMESPACE__.'\\'.ucfirst($action).'Package');
        $this->getApplication()->addCommands([$command]);

        $args = [];

        if ($this->argument('package')) {
            $args['package'] = $this->argument('package');
        }

        foreach ($this->options() as $k => $v) {
            if($v !== false) {
                $args['--'.$k] = $v;
            }
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
        $this->line('  <fg=green>boilerplate:package crud <package></>                         Create the files for crud from package tables.');
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

    protected function configureUsingFluentDefinition()
    {
        parent::configureUsingFluentDefinition();

        if (isset($_SERVER['argv'][2]) && $action = $_SERVER['argv'][2]) {
            $class = __NAMESPACE__.'\\'.ucfirst($action).'Package';

            if (class_exists($class)) {
                $this->laravel = app();

                $command = $this->resolveCommand($class);

                [$name, $arguments, $options] = Parser::parse($command->getSignature());

                $this->getDefinition()->addOptions($options);
            }
        }
    }
}
