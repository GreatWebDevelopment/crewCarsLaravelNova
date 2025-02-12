<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('gallerys', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');
            $table->integer('carId');
            $table->text('img');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gallerys');
    }
};
