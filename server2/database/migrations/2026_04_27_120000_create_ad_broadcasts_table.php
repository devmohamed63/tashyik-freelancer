<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('audience', 32);
            $table->string('title');
            $table->string('description', 512)->nullable();
            $table->string('image_path')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ad_broadcasts');
    }
};
