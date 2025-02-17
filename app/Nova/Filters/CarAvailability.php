<?php

namespace App\Nova\Filters;

use Laravel\Nova\Filters\Filter;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Contracts\Database\Eloquent\Builder;

class CarAvailability extends Filter
{
    public function apply(NovaRequest $request, Builder $query, mixed $value): Builder
    {
        return $query->where('status', $value);
    }

    public function options(NovaRequest $request)
    {
        return [
            'Available' => 1,
            'Unavailable' => 0
        ];
    }
}
