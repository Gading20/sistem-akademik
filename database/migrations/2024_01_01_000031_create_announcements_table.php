<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->json('target_roles')->nullable();
            $table->json('target_class_ids')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('attachment_path')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
