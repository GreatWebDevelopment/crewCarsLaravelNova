<?php

namespace App\Nova;

use Illuminate\Http\Request;
use App\Nova\Booking;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use App\Nova\Metrics\CarsAddedTrend;
use App\Nova\Metrics\TotalCars;
use App\Nova\Metrics\CarsByBrand;
use App\Nova\Metrics\CarsAvailability;

use Laravel\Nova\Fields\Hidden;
use Laravel\Nova\Panel;
use App\Nova\User;
use App\Nova\Metrics\CarsAddedTrend;
use App\Nova\Metrics\TotalCars;
use App\Nova\Metrics\CarsByBrand;
use App\Nova\Metrics\CarsAvailability;


class Car extends Resource
{


    /**
     * The model the resource corresponds to.
     *
     * @var class-string<\App\Models\Car>
     */
    public static $model = \App\Models\Car::class;

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
        return [
            ID::make()->sortable(),
            Text::make('Title')
                ->sortable()
                ->rules('required', 'max:255'),

            Image::make('Image', 'img')
                ->disk('public'),

            Text::make('Number') // Car Plate or Identifier
            ->sortable()
                ->rules('required', 'max:50'),

            BelongsTo::make('Owner', 'user', 'App\Nova\User')
                ->sortable()
                ->searchable()
                ->rules('required'),

            Number::make('Seats')
                ->sortable()
                ->hideFromIndex()
                ->rules('integer', 'min:1', 'max:10'),

            Boolean::make('AC')
                ->hideFromIndex()
                ->sortable(),

            Text::make('Driver Name', 'driverName')
                ->hideFromIndex()
                ->rules('nullable', 'max:255'),

            Number::make('Rent Price', 'rentPrice')
                ->sortable()
                ->rules('required', 'numeric', 'min:0'),

            Panel::make('Location Details', [
                Text::make('Location', 'location')
                    ->help('Start typing and select a location from Google Places.')
                    ->withMeta(['extraAttributes' => [
                        'id' => 'location-input'
                    ]]),

                Hidden::make('Latitude', 'latitude'),
                Hidden::make('Longitude', 'longitude'),
            ]),

            HasMany::make('Bookings', 'bookings', 'App\Nova\Booking'),
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
            new CarsAddedTrend(),
            new TotalCars(),
            //new CarsByBrand(),
            //new CarsAvailability(),
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
