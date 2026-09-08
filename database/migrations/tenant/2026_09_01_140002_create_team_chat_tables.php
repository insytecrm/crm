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
        if (! Schema::hasTable('team_conversations')) {
            Schema::create('team_conversations', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('team_conversation_participants')) {
            Schema::create('team_conversation_participants', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_conversation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['team_conversation_id', 'user_id'], 'team_conv_participants_unique');
            });
        }

        if (! Schema::hasTable('team_messages')) {
            Schema::create('team_messages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('team_conversation_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body')->nullable();
                $table->json('attachments')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('team_messages');
        Schema::dropIfExists('team_conversation_participants');
        Schema::dropIfExists('team_conversations');
    }
};
