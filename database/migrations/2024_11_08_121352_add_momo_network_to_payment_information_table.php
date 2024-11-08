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
        Schema::table('payment_information', function (Blueprint $table) {
            $table->string('momo_network')->nullable()->after('momo_acc_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_information', function (Blueprint $table) {
            $table->dropColumn('momo_network');
        });
    }
};
