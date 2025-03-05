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
            $table->dropForeign('cars_type_foreign');
            $table->dropForeign('cars_brand_foreign');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('car', function (Blueprint $table) {
            $table->foreign('cars_type_foreign')->references('id')->on('carTypes');
            $table->foreign('cars_brand_foreign')->references('id')->on('carBrands');
        });
    }
};
