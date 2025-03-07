<?php

namespace App\Nova;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Resource;
use Laravel\Nova\Http\Requests\NovaRequest;
use Illuminate\Support\Facades\Log;
use App\Nova\Car;
use App\Nova\Metrics\TotalBookings;
use App\Nova\Metrics\BookingsOverTime;
use App\Nova\Metrics\BookingsByStatus;
use App\Nova\Metrics\BookingRevenue;

class Booking extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Booking>
     */
    public static $model = \App\Models\Booking::class;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        $carField = BelongsTo::make('Car', 'car', Car::class);

        return [
            ID::make()->sortable(),
            BelongsTo::make('User', 'user', 'App\Nova\User'),
            $carField,
            Number::make('Total Price', 'oTotal')->sortable(),
            Text::make('Booking Status', 'bookingStatus')->sortable(),
            Boolean::make('Is Rated', 'isRate'),
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [
            new TotalBookings(),
            new BookingsOverTime(),
            new BookingsByStatus(),
            new BookingRevenue(),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array<int, \Laravel\Nova\Filters\Filter>
     */
    public function filters(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array<int, \Laravel\Nova\Lenses\Lens>
     */
    public function lenses(NovaRequest $request): array
    {
        return [];
    }

    /**
     * Get the actions available for the resource.
     *
     * @return array<int, \Laravel\Nova\Actions\Action>
     */
    public function actions(NovaRequest $request): array
    {
        return [];
    }
}
