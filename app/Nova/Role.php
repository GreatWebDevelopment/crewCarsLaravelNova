<?php
    namespace App\Nova;

    use Laravel\Nova\Fields\ID;
    use Laravel\Nova\Fields\Text;
    use Laravel\Nova\Fields\BelongsToMany;
    use Laravel\Nova\Resource;
    use Laravel\Nova\Http\Requests\NovaRequest;

    class Role extends Resource
    {
        public static $model = \Spatie\Permission\Models\Role::class; // ✅ Use the correct Eloquent model

        public static $title = 'name';

        public function fields(NovaRequest $request)
        {
            return [
                ID::make()->sortable(),

                Text::make('Name')
                    ->sortable()
                    ->rules('required', 'max:255'),

                // ✅ Link Roles to Users
                BelongsToMany::make('Users', 'users', User::class),
            ];
        }
    }
