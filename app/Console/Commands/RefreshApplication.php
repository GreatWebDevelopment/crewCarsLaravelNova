<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RefreshApplication extends Command
{
    protected $signature = 'app:refresh';
    protected $description = 'Clears cache, config, routes, views, and restarts the Laravel server';

    public function handle()
    {
        $this->call('cache:clear');
        $this->call('config:clear');
        $this->call('optimize:clear');
        $this->call('route:clear');
        $this->call('view:clear');
        $this->call('nova:publish');
        $this->call('nova:cache');



        $this->info('Application cache and config cleared.');

        // Restart Laravel server (optional)
        exec('herd restart');

        return Command::SUCCESS;
    }
}
