<?php

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
        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'service_id')) {
                $table->foreignId('service_id')->nullable()->after('id')->constrained('services')->nullOnDelete();
            }
            if (!Schema::hasColumn('articles', 'category_id')) {
                $table->foreignId('category_id')->nullable()->after('service_id')->constrained('categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('articles', 'generated_by_ai')) {
                $table->boolean('generated_by_ai')->default(false)->after('is_featured');
            }
            if (!Schema::hasColumn('articles', 'meta_keywords')) {
                $table->json('meta_keywords')->nullable()->after('meta_description');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'ai_blog_automation_enabled')) {
                $table->boolean('ai_blog_automation_enabled')->default(false)->after('email');
            }
            if (!Schema::hasColumn('settings', 'ai_blog_daily_limit')) {
                $table->unsignedInteger('ai_blog_daily_limit')->default(5)->after('ai_blog_automation_enabled');
            }
            if (!Schema::hasColumn('settings', 'ai_blog_monthly_limit')) {
                $table->unsignedInteger('ai_blog_monthly_limit')->default(100)->after('ai_blog_daily_limit');
            }
            if (!Schema::hasColumn('settings', 'ai_blog_prompt')) {
                $table->text('ai_blog_prompt')->nullable()->after('ai_blog_monthly_limit');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'service_id')) {
                $table->dropForeign(['service_id']);
                $table->dropColumn('service_id');
            }
            if (Schema::hasColumn('articles', 'category_id')) {
                $table->dropForeign(['category_id']);
                $table->dropColumn('category_id');
            }
            if (Schema::hasColumn('articles', 'generated_by_ai')) {
                $table->dropColumn('generated_by_ai');
            }
            if (Schema::hasColumn('articles', 'meta_keywords')) {
                $table->dropColumn('meta_keywords');
            }
        });

        Schema::table('settings', function (Blueprint $table) {
            $cols = ['ai_blog_automation_enabled', 'ai_blog_daily_limit', 'ai_blog_monthly_limit', 'ai_blog_prompt'];
            $existing = array_filter($cols, fn($c) => Schema::hasColumn('settings', $c));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
