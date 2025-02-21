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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('userId')->constrained('users')->onDelete('cascade');
            $table->foreignId('bookingId')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('paymentMethodId')->constrained('paymentMethods')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->dateTime('paymentDate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
