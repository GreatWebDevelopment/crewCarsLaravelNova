<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Dashboard;
use Laravel\Nova\Cards\Help;

class Main extends Dashboard
{
    public function name()
    {
        return 'Main Dashboard';
    }

    public function cards()
    {
        return [
            new Help(),
        ];
    }
}
