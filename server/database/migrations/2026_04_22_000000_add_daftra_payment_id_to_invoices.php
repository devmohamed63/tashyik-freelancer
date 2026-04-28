<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'daftra_payment_id')) {
                $table->unsignedBigInteger('daftra_payment_id')
                    ->nullable()
                    ->after('daftra_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'daftra_payment_id')) {
                $table->dropColumn('daftra_payment_id');
            }
        });
    }
};
