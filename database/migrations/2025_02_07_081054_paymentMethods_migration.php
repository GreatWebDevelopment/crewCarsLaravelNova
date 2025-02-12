<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('paymentMethods', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100);
            $table->string('image', 255);
            $table->string('attributes', 255)->nullable();
            $table->boolean('status')->default(1);
            $table->string('subtitle', 255)->nullable();
            $table->boolean('isVisible')->default(1);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('paymentMethods');
    }
};
