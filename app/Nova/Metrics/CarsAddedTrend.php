<?php

namespace App\Nova\Metrics;

use DateTimeInterface;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;
use Laravel\Nova\Nova;
use App\Models\Car;

class CarsAddedTrend extends Trend
{
    public function name()
    {
        return 'Cars Added Over Time';
    }

    /**
     * Calculate the value of the metric.
     */
    public function calculate(NovaRequest $request)
    {
        return $this->countByMonths($request, Car::class);
    }

    /**
     * Get the ranges available for the metric.
     *
     * @return array<int, string>
     */
    public function ranges(): array
    {
        return [
            30 => 'Last 30 Days',
            60 => 'Last 60 Days',
            365 => 'Last Year',
        ];
    }

    /**
     * Determine the amount of time the results of the metric should be cached.
     */
    public function cacheFor(): DateTimeInterface|null
    {
        // return now()->addMinutes(5);

        return null;
    }

    /**
     * Get the URI key for the metric.
     */
    public function uriKey(): string
    {
        return 'cars-added-trend';
    }
}
