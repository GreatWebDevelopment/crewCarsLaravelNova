<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('walletReports', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');
            $table->text('message');
            $table->text('status');
            $table->float('amt');
            $table->date('tdate');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('walletReports');
    }
};
