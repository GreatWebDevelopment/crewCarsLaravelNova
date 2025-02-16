<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('number', 100);
            $table->string('img',255);
            $table->boolean('status');
            $table->float('rating');
            $table->integer('seats')->default(4)->default(4);
            $table->boolean('ac')->default(true);
            $table->string('driverName', 100);
            $table->string('driverMobile', 13);
            $table->string('transmission', 50);
            $table->string('facility', 100);
            $table->string('type', 50);
            $table->string('brand', 50);
            $table->boolean('available')->default(true);
            $table->decimal('rentPrice', 10, 2);
            $table->decimal('rentPriceDriver', 10, 2);
            $table->integer('engineHp');
            $table->integer('priceType');
            $table->string('fuelType', 50);
            $table->string('location', 100);
            $table->text('description');
            $table->string('pickAddress', 255);
            $table->decimal('pickLat', 10, 8);
            $table->decimal('pickLng', 11, 8);
            $table->integer('totalMiles');
            $table->integer('postId');
            $table->integer('minHrs');
            $table->integer('isApproved');
            $table->text('rejectComment')->nullable();
            $table->integer('mileage')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cars');
    }
};
