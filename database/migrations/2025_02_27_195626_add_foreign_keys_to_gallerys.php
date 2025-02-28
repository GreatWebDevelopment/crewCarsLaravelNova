<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('gallerys', function (Blueprint $table) {
            $table->unsignedBigInteger('uid')->change();
            $table->unsignedBigInteger('carId')->change();
            $table->foreign('uid')->references('id')->on('users');
            $table->foreign('carId')->references('id')->on('cars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallerys', function (Blueprint $table) {
            //
        });
    }
};
