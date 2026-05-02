<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Marks customers created via the bulk-import flow.
            // Used to identify placeholder accounts that still need to be claimed by their owners.
            $table->timestamp('imported_at')->nullable()->after('last_seen_at')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['imported_at']);
            $table->dropColumn('imported_at');
        });
    }
};
