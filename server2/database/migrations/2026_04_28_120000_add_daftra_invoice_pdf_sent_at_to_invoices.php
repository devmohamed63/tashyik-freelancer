<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('invoices', 'daftra_invoice_pdf_sent_at')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->timestamp('daftra_invoice_pdf_sent_at')->nullable()->after('daftra_payment_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'daftra_invoice_pdf_sent_at')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('daftra_invoice_pdf_sent_at');
            });
        }
    }
};
