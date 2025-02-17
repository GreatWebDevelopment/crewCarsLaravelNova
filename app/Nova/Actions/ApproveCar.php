<?php

namespace App\Nova\Actions;

use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Facades\DB;

class ApproveCar extends Action
{
    public function handle(ActionFields $fields, $models)
    {
        foreach ($models as $car) {
            $car->update(['status' => 1]);
        }

        return Action::message('Car approved successfully!');
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
    public function authorize(NovaRequest $request): bool
    {
        return $request->user()->hasRole('admin');
    }
}
