<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('carId')->nullable()->constrained('cars')->nullOnDelete();
                $table->foreignId('uid')->nullable()->constrained('users')->nullOnDelete();
                $table->integer('cityId')->nullable();
                $table->decimal('carPrice', 10, 2)->default(0.00);
                $table->string('priceType', 50);
                $table->date('pickupDate');
                $table->string('pickupTime', 10);
                $table->date('returnDate')->nullable();
                $table->string('returnTime', 10)->nullable();
                $table->integer('couId')->nullable();
                $table->decimal('couAmt', 10, 2)->default(0.00);
                $table->decimal('wallAmt', 10, 2)->default(0.00);
                $table->integer('totalDayOrHr')->default(1);
                $table->decimal('subtotal', 10, 2)->default(0.00);
                $table->decimal('taxPer', 5, 2)->default(0.00);
                $table->decimal('taxAmt', 10, 2)->default(0.00);
                $table->decimal('oTotal', 10, 2)->default(0.00);
                $table->foreignId('pMethodId')->nullable()->constrained('paymentMethods')->nullOnDelete();
                $table->string('transactionId', 255)->nullable();
                $table->integer('typeId')->nullable();
                $table->integer('brandId')->nullable();
                $table->string('bookingType', 50);
                $table->string('bookingStatus', 50)->default('pending');
                $table->boolean('isRate')->default(0);
                $table->decimal('totalRate', 3, 2)->default(0.00);
                $table->text('rateText')->nullable();
                $table->string('exterPhoto', 255)->nullable();
                $table->string('interPhoto', 255)->nullable();
                $table->date('reviewDate')->nullable();
                $table->text('cancelReason')->nullable();
                $table->integer('postId')->nullable();
                $table->decimal('commission', 10, 2)->default(0.00);
                $table->integer('pickOtp')->nullable();
                $table->integer('dropOtp')->nullable();
                $table->timestamps();
            });
    }

    public function down()
    {
        Schema::dropIfExists('bookings');
    }
};
