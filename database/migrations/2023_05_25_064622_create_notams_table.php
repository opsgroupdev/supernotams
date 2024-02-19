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
            $table->json('structure');
            $table->string('code', 4)->nullable();
            $table->string('type')->nullable();
            $table->text('summary')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notams');
    }
};
