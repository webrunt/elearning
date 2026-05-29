<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('video_processing_status')->nullable()->after('video_disk');
            $table->text('video_processing_error')->nullable()->after('video_processing_status');
            $table->timestamp('video_processed_at')->nullable()->after('video_processing_error');
            $table->string('audio_path')->nullable()->after('video_processed_at');
            $table->string('audio_disk')->nullable()->after('audio_path');
        });
    }

    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn([
                'video_processing_status',
                'video_processing_error',
                'video_processed_at',
                'audio_path',
                'audio_disk',
            ]);
        });
    }
};
