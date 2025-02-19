<?php

namespace App\Nova\Dashboards;

use Laravel\Nova\Cards\Help;
//use Laravel\Nova\Dashboards\Main as Dashboard;
use Laravel\Nova\Dashboard;


class Main extends Dashboard
{

    public function name()
    {
        return 'Main Dashboard';
    }
    /**
     * Get the cards for the dashboard.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(): array
    {
        return [
            new Help,
        ];
    }
}
