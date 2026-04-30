<?php

use App\Models\Invoice;
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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id()->startingValue(1000);
            $table->foreignId('service_provider_id')->constrained('users')->cascadeOnDelete();
            $table->integer('target_id')->nullable();
            $table->enum('type', Invoice::AVAILABLE_TYPES);
            $table->enum('action', Invoice::AVAILABLE_ACTIONS);
            $table->decimal('amount', 8, config('app.decimal_places'));
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
