<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Envoy extends Command
{
    /**
     * The name and signature of the console command. Note that options aren't supported for this console command. (I couldn't figure out how to do it.)
     *
     * @var string
     */
    protected $signature = 'envoy {all_commands*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute envoy commands';

    /**
     * The location of the envoy package.
     *
     * @var string
     */
    protected $envoy_path = 'vendor/bin/envoy';

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
     * @return mixed
     */
    public function handle()
    {
        // get the arguments for this command
        $arguments = $this->argument('all_commands');

        // build up the command string
        $command = $this->envoy_path . " " . implode(" ", $arguments);

        $this->info(shell_exec($command));
    }
}
