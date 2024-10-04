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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique()->nullable();
            $table->string('email', '255')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('ghana_post_gps')->nullable();
            $table->string('city')->nullable();
            $table->text('physical_address')->nullable();
            $table->string('password')->nullable();
            $table->boolean('creative_hire_status')->default(false);
            $table->string('creative_status')->nullable();
            $table->string('profile_picture')->nullable();
//            $table->string('profile_photo_path', 2048)->nullable();
            $table->unsignedBigInteger('hiring_id')->nullable();
            $table->text('description')->nullable(); // for creatives
            $table->string('google_id')->nullable()->unique();
            $table->string('type')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
    }
};
