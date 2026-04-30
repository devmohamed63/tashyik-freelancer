<?php

use App\Models\Order;
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
            $table->id()->startingValue(1000);
            $table->foreignId('customer_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignId('service_provider_id')->nullable()->constrained('users', 'id')->nullOnDelete();
            $table->foreignId('address_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->decimal('visit_cost', 8, config('app.decimal_places'));
            $table->decimal('subtotal', 8, config('app.decimal_places'));
            $table->integer('tax_rate');
            $table->decimal('tax', 8, config('app.decimal_places'));
            $table->decimal('coupons_total', 8, config('app.decimal_places'));
            $table->decimal('wallet_balance', 8, config('app.decimal_places'));
            $table->decimal('total', 8, config('app.decimal_places'));
            $table->enum('status', Order::AVAILABLE_STATUS_TYPES)->default(Order::NEW_STATUS);
            $table->text('service_provider_notes')->nullable();
            $table->timestamps();
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
