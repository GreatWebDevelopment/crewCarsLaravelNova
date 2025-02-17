<?php

namespace App\Nova\Metrics;

use Laravel\Nova\Metrics\Value;
use App\Models\Car;
use Laravel\Nova\Http\Requests\NovaRequest;

class TotalCars extends Value
{
    public function calculate(NovaRequest $request)
    {
        return $this->count($request, Car::class);
    }

    public function name()
    {
        return 'Total Cars';
    }
}
