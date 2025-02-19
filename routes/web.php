<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Laravel\Nova\Nova;

Route::get('/login', function () {
    return redirect('/nova');
});
Route::middleware(['auth'])->group(function () {

    Nova::routes();
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

});
Route::get('/', function () {
    return redirect('/nova');
});

Route::get('/dashboard', function () {
    return redirect('/nova');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/auth.php';
