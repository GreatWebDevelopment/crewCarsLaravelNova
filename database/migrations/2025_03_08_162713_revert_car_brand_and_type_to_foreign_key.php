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
        Schema::table('cars', function (Blueprint $table) {
            $table->unsignedBigInteger('brand')->change();
            $table->unsignedBigInteger('type')->change();

            $table->foreign('brand')->references('id')->on('carBrands');
            $table->foreign('type')->references('id')->on('carTypes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropForeign(['brand', 'type']);
        });
    }
};
