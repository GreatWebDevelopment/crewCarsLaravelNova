<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->text('img');
            $table->text('title');
            $table->text('code');
            $table->text('subtitle');
            $table->date('expireDate');
            $table->float('minAmt');
            $table->float('value');
            $table->text('description');
            $table->integer('status');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('coupons');
    }
};
