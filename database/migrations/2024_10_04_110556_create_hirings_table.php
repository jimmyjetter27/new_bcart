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
        Schema::create('hirings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creative_id');
            $table->unsignedBigInteger('regular_user_id');
            $table->date('hire_date');
            $table->string('location');
            $table->unsignedInteger('num_days');
            $table->unsignedInteger('num_hours');
            $table->text('description');
            $table->timestamps();

            $table->foreign('creative_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('regular_user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('hiring_categories', function (Blueprint $table) {
            $table->unsignedBigInteger('hiring_id');
            $table->unsignedBigInteger('creative_category_id');
            $table->timestamps();

            $table->foreign('hiring_id')->references('id')->on('hirings')->onDelete('cascade');
            $table->foreign('creative_category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hirings');
    }
};
