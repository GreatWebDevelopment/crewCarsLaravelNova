<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('payoutSettings', function (Blueprint $table) {
            $table->id();
            $table->integer('uid');
            $table->float('amt');
            $table->text('status');
            $table->text('proof');
            $table->dateTime('rDate');
            $table->string('rType');
            $table->text('accNumber');
            $table->text('bankName');
            $table->text('accName');
            $table->text('ifscCode');
            $table->text('upiId');
            $table->text('paypalId');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payoutSettings');
    }
};
