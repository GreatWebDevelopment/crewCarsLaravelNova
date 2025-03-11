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
            $table->dropForeign('cars_brand_foreign');
            $table->dropForeign('cars_type_foreign');
            $table->dropColumn('type');
            $table->dropColumn('brand');

            $table->unsignedBigInteger('typeId');
            $table->unsignedBigInteger('brandId');
            $table->foreign('brandId')->references('id')->on('carBrands');
            $table->foreign('typeId')->references('id')->on('carTypes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            //
        });
    }
};
