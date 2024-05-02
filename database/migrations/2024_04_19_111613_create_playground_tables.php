<?php

use App\Models\PlaygroundSession;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playground_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->ipAddress()->nullable();
            $table->timestamps();
        });

        Schema::create('playground_notams', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignIdFor(PlaygroundSession::class, 'session_id');
            $table->text('text');
            $table->string('tag', 2)->nullable();
            $table->string('summary')->nullable();
            $table->tinyInteger('llm')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playground_sessions');

        Schema::dropIfExists('playground_notams');
    }
};
