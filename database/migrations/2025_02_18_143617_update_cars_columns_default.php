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
            $table->tinyInteger('rating')->default(0)->change();
            $table->string('driverMobile')->default('')->change();
            $table->string('transmission')->default('')->change();
            $table->string('facility')->default('')->change();
            $table->string('type')->default('')->change();
            $table->tinyInteger('available')->default(0)->change();
            $table->decimal('rentPriceDriver')->default(0)->change();
            $table->integer('engineHp')->default(0)->change();
            $table->integer('priceType')->default(0)->change();
            $table->string('fuelType')->default('')->change();
            $table->string('carDesc')->default('');
            $table->string('pickAddress')->default('')->change();
            $table->decimal('pickLat')->default(0)->change();
            $table->decimal('pickLng')->default(0)->change();
            $table->integer('totalMiles')->default(0)->change();
            $table->integer('postId')->default(0)->change();
            $table->integer('minHrs')->default(0)->change();
            $table->integer('isApproved')->default(0)->change();
            $table->string('rejectComment')->default('')->change();
            $table->integer('mileage')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->tinyInteger('rating')->default(null)->change();
            $table->string('driverMobile')->default(null)->change();
            $table->string('transmission')->default(null)->change();
            $table->string('facility')->default(null)->change();
            $table->string('type')->default(null)->change();
            $table->tinyInteger('available')->default(null)->change();
            $table->decimal('rentPriceDriver')->default(null)->change();
            $table->integer('engineHp')->default(null)->change();
            $table->integer('priceType')->default(null)->change();
            $table->string('fuelType')->default(null)->change();
            $table->string('pickAddress')->default(null)->change();
            $table->decimal('pickLat')->default(null)->change();
            $table->decimal('pickLng')->default(null)->change();
            $table->integer('totalMiles')->default(null)->change();
            $table->integer('postId')->default(null)->change();
            $table->integer('minHrs')->default(null)->change();
            $table->integer('isApproved')->default(null)->change();
            $table->string('rejectComment')->default(null)->change();
            $table->integer('mileage')->default(null)->change();
        });
    }
};
