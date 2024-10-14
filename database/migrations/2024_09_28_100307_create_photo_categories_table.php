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
        Schema::create('photo_categories', function (Blueprint $table) {
            $table->id();
            $table->string('image_public_id')->nullable();
            $table->string('image_url')->nullable();
//            $table->string('photo_category_picture')->nullable();
            $table->string('photo_category')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_categories');
    }
};
