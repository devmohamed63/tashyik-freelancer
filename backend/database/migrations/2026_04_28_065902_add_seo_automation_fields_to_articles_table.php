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
            $table->foreignId('service_id')->nullable()->after('id')->constrained('services')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('service_id')->constrained('categories')->nullOnDelete();
            $table->boolean('generated_by_ai')->default(false)->after('is_featured');
            $table->json('meta_keywords')->nullable()->after('meta_description');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('ai_blog_automation_enabled')->default(false)->after('email');
            $table->unsignedInteger('ai_blog_daily_limit')->default(5)->after('ai_blog_automation_enabled');
            $table->unsignedInteger('ai_blog_monthly_limit')->default(100)->after('ai_blog_daily_limit');
            $table->text('ai_blog_prompt')->nullable()->after('ai_blog_monthly_limit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['service_id', 'category_id', 'generated_by_ai', 'meta_keywords']);
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'ai_blog_automation_enabled',
                'ai_blog_daily_limit',
                'ai_blog_monthly_limit',
                'ai_blog_prompt',
            ]);
        });
    }
};
