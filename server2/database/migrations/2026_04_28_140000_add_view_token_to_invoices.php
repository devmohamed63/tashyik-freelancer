<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('view_token', 64)->nullable()->unique()->after('id');
        });

        $ids = DB::table('invoices')->whereNull('view_token')->pluck('id');
        foreach ($ids as $id) {
            $token = self::randomUniqueToken();
            DB::table('invoices')->where('id', $id)->update(['view_token' => $token]);
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropUnique(['view_token']);
            $table->dropColumn('view_token');
        });
    }

    private static function randomUniqueToken(): string
    {
        for ($i = 0; $i < 30; $i++) {
            $token = Str::lower(Str::random(32));
            if (! DB::table('invoices')->where('view_token', $token)->exists()) {
                return $token;
            }
        }

        throw new \RuntimeException('Unable to assign unique view_token during migration.');
    }
};
