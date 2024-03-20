<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notams', function (Blueprint $table) {
            $table->string('id', 20)->unique();
            $table->longText('fullText')->nullable();
            $table->string('code', 2)->nullable();
            $table->string('type')->nullable();
            $table->text('summary')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->tinyInteger('llm')->nullable();
            $table->json('source');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notams');
    }
};
