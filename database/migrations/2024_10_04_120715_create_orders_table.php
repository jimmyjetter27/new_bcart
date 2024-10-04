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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('users');
            $table->morphs('orderable'); // This will track what type of order (Photo, Hiring, etc.)
            $table->string('order_number')->unique();
            $table->decimal('total_price', 8, 2);
            $table->decimal('discount_price', 8, 2)->nullable();
            $table->enum('transaction_status', ['pending', 'completed', 'failed']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
