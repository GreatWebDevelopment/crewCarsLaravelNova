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
        Schema::create('pilotCertificates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId');
            $table->string('name');
            $table->string('certificateNumber');
            $table->text('s3Key');
            $table->text('address')->nullable();
            $table->string('sex')->nullable();
            $table->string('height')->nullable();
            $table->string('weight')->nullable();
            $table->string('hair')->nullable();
            $table->string('eyes')->nullable();
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
        Schema::dropIfExists('pilotCertificates');
    }
};
