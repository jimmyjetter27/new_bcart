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
        Schema::create('pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('creative_id')->constrained('users')->onDelete('cascade');
            $table->decimal('hourly_rate', 8, 2)->nullable();
            $table->decimal('daily_rate', 8, 2)->nullable();
            $table->decimal('minimum_charge', 8, 2)->nullable();

            // Wedding-specific pricing columns
            $table->decimal('one_day_traditional', 8, 2)->nullable();
            $table->decimal('one_day_white', 8, 2)->nullable();
            $table->decimal('one_day_white_traditional', 8, 2)->nullable();
            $table->decimal('two_days_white_traditional', 8, 2)->nullable();
            $table->decimal('three_days_thanksgiving', 8, 2)->nullable();
//            $table->decimal('other_charges', 8, 2)->nullable();
            $table->text('other_charges')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricings');
    }
};
