<?php

namespace App\Nova;

use Illuminate\Http\Request;
use Laravel\Nova\Fields\Gravatar;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Http\Requests\NovaRequest;

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
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * Indicates whether the resource should automatically poll for new resources.
     *
     * @var bool
     */
    public static $polling = true;

    /**
     * Get the fields displayed by the resource.
     *
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    public function fields(NovaRequest $request): array
    {
        return [
            ID::make()->sortable(),

            Gravatar::make()->maxWidth(50),
/*
            Text::make('Title')
                ->sortable()
                ->rules('required', 'max:100'),

            Text::make('Number')
                ->sortable()
                ->rules('required', 'max:100'),

            Text::make('Image', 'img')
                ->rules('required', 'max:255'),

            Number::make('Status')
                ->sortable()
                ->min(-128)->max(127)
                ->rules('required'),

            Number::make('Rating')
                ->sortable()
                ->rules('required'),

            Number::make('Seats')
                ->sortable()
                ->rules('required'),

            Number::make('Air Conditioning', 'ac')
                ->sortable()
                ->min(-128)->max(127)
                ->rules('required'),

            Text::make('Driver Name', 'driverName')
                ->sortable()
                ->rules('required', 'max:100'),

            Text::make('Driver Mobile', 'driverMobile')
                ->rules('required', 'max:13'),

            Text::make('Transmission')
                ->sortable()
                ->rules('required', 'max:50'),

            Text::make('Facility')
                ->sortable()
                ->rules('required', 'max:100'),

            Text::make('Type')
                ->sortable()
                ->rules('required', 'max:50'),

            Text::make('Brand')
                ->sortable()
                ->rules('required', 'max:50'),

            Number::make('Available')
                ->sortable()
                ->rules('required'),

            Number::make('Rent Price', 'rentPrice')
                ->sortable()
                ->rules('required'),

            Number::make('Rent Price Driver', 'rentPriceDriver')
                ->sortable()
                ->rules('required'),

            Number::make('Engine Hp', 'engineHp')
                ->sortable()
                ->rules('required'),

            Number::make('Price Type', 'priceType')
                ->sortable()
                ->rules('required'),

            Text::make('Fuel Type', 'fuelType')
                ->sortable()
                ->rules('required', 'max:50'),

            Text::make('Location')
                ->rules('required', 'max:100'),

            Text::make('Car Description', 'carDesc')
                ->rules('required', 'max:255'),

            Text::make('Pick Address', 'pickAddress')
                ->rules('required', 'max:255'),

            Number::make('Pick Latitude', 'pickLat')
                ->rules('required'),

            Number::make('Pick Longitude', 'pickLng')
                ->rules('required'),

            Number::make('Total Miles', 'totalMiles')
                ->rules('required'),

            Number::make('Post ID', 'postId')
                ->rules('required'),

            Number::make('Min Hrs', 'minHrs')
                ->rules('required'),

            Number::make('IsApproved', 'isApproved')
                ->rules('required'),

            Text::make('Reject Comment', 'rejectComment'),

            Number::make('Mileage')*/
        ];
    }

    /**
     * Get the cards available for the resource.
     *
     * @return array<int, \Laravel\Nova\Card>
     */
    public function cards(NovaRequest $request): array
    {
        return [];
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

    /**
     * Get the menu that should represent the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Laravel\Nova\Menu\MenuItem
     */
    public function menu(Request $request)
    {
        return parent::menu($request)->withBadge(function () {
            return static::$model::count();
        });
    }
}
