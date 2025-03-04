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
        Schema::table('payoutSettings', function (Blueprint $table) {
            $table->text('proof')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payoutSettings', function (Blueprint $table) {
            $table->text('proof')->nullable(true)->change();
        });
    }
};
