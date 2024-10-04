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
        Schema::create('creative_category_creative', function (Blueprint $table) {
            $table->unsignedBigInteger('creative_id');
            $table->unsignedBigInteger('creative_category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('creative_category_creative', function (Blueprint $table) {
            //
        });
    }
};
