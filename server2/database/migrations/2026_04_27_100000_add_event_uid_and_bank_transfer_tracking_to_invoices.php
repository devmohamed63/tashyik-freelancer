<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('event_uid')->nullable()->after('target_id');
            $table->boolean('recorded_in_daftra')->default(false)->after('daftra_payment_id');
            $table->timestamp('recorded_in_daftra_at')->nullable()->after('recorded_in_daftra');
            $table->foreignId('recorded_in_daftra_by')
                ->nullable()
                ->after('recorded_in_daftra_at')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('invoices')
            ->select(['id', 'type'])
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('invoices')
                        ->where('id', $row->id)
                        ->update([
                            'event_uid' => "legacy:invoice:{$row->id}:type:{$row->type}",
                        ]);
                }
            });

        Schema::table('invoices', function (Blueprint $table) {
            $table->unique('event_uid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['event_uid']);
            $table->dropConstrainedForeignId('recorded_in_daftra_by');
            $table->dropColumn([
                'event_uid',
                'recorded_in_daftra',
                'recorded_in_daftra_at',
            ]);
        });
    }
};
