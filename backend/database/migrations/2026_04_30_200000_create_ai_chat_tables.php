<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_chat_conversations')) {
            Schema::create('ai_chat_conversations', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('guest_token')->nullable()->index();
                $table->string('status')->default('open')->index();
                $table->timestamp('last_message_at')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('ai_chat_messages')) {
            Schema::create('ai_chat_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('conversation_id')->constrained('ai_chat_conversations')->cascadeOnDelete();
                $table->string('sender_type'); // user, ai, system
                $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
                $table->text('content');
                $table->string('content_type')->default('text');
                $table->string('intent')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->index(['conversation_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_messages');
        Schema::dropIfExists('ai_chat_conversations');
    }
};
