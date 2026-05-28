<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('type')->default('video');
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->string('video_path')->nullable();
            $table->string('video_disk')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->boolean('require_quiz_to_complete')->default(false);
            $table->unsignedTinyInteger('quiz_pass_percent')->default(70);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
