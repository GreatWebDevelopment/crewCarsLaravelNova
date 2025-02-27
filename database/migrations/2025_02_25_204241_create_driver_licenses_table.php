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
        Schema::create('driverLicenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('name');
            $table->string('licenseNumber');
            $table->text('s3Key');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('class')->nullable();
            $table->string('restrictions')->nullable();
            $table->string('country')->nullable();
            $table->string('placeOfBirth')->nullable();
            $table->integer('zipCode')->nullable();
            $table->date('dayOfBirth')->nullable();
            $table->date('issueDate')->nullable();
            $table->date('expireDate')->nullable();
            $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driverLicenses');
    }
};
