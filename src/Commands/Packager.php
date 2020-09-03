<?php

namespace Sebastienheyd\BoilerplatePackager\Commands;

use Illuminate\Console\Command;

class Packager extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'boilerplate:packager {action : new, get, remove} {package? : package name} {--dev : put package in require-dev section}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage package for sebastienheyd/boilerplate';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');

        if(!in_array($action, ['new', 'get', 'remove', 'list'])) {
            $this->error('Action not found');
            exit;
        }

        $this->getApplication()->addCommands([$this->resolveCommand(__NAMESPACE__.'\\'.ucfirst($action).'Package')]);

        $args = [];

        if($this->argument('package')) {
            $args['package'] = $this->argument('package');
        }

        if($this->option('dev')) {
            $args['--dev'] = true;
        }

        $this->call('boilerplate:packager:'.$action, $args);
    }
}
