<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text('webname');
            $table->text('weblogo');
            $table->text('timezone');
            $table->text('currency');
            $table->text('oneKey');
            $table->text('oneHash');
            $table->integer('scredit');
            $table->integer('rcredit');
            $table->integer('showDark');
            $table->float('tax');
            $table->integer('showAddCar');
            $table->float('wlimit');
            $table->float('commissionRate');
            $table->text('contactNo');
            $table->text('apiKey');
            $table->text('smsType');
            $table->text('authKey');
            $table->text('otpId');
            $table->text('accId');
            $table->text('authToken');
            $table->text('twilioNumber');
            $table->text('otpAuth');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
