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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
//            $table->foreignId('payment_information_id')->constrained('payment_information');
            $table->string('transaction_id')->unique();
            $table->string('payment_method');  // e.g., 'momo', 'bank', 'card', etc.
            $table->decimal('amount', 8, 2);  // Amount paid
            $table->enum('status', ['pending', 'completed', 'failed']);  // Payment status
            $table->timestamp('transaction_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
