<?php

use App\Models\OrderExtra;
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
        Schema::create('order_extras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->enum('status', OrderExtra::AVAILABLE_STATUS_TYPES);
            $table->integer('quantity');
            $table->decimal('price', 8, config('app.decimal_places'));
            $table->integer('tax_rate');
            $table->decimal('tax', 8, config('app.decimal_places'));
            $table->decimal('materials', 8, config('app.decimal_places'))->nullable();
            $table->decimal('wallet_balance', 8, config('app.decimal_places'))->nullable();
            $table->decimal('total', 8, config('app.decimal_places'))->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_extras');
    }
};
