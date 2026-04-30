<?php

use App\Models\User;
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
            $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('institution_id')->nullable()->constrained('users')->nullOnDelete();

            // Basic information
            $table->string('name');
            $table->string('phone')->unique();
            $table->string('password');
            $table->enum('type', User::AVAILABLE_ACCOUNT_TYPES)->default(User::USER_ACCOUNT_TYPE);

            // More information
            $table->enum('entity_type', User::AVAILABLE_ENTITY_TYPES)->nullable();
            $table->string('residence_name')->nullable();
            $table->string('residence_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('iban')->nullable();
            $table->string('commercial_registration_number')->nullable();
            $table->string('tax_registration_number')->nullable();

            $table->enum('status', User::AVAILABLE_STATUS_TYPES)->default(User::ACTIVE_STATUS);
            $table->string('ui_locale')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->decimal('balance', 8, config('app.decimal_places'))->default(0);
            $table->boolean('used_welcome_coupon')->default(false);
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
