<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wall_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending')->index();
            $table->string('source')->default('form');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->text('body');
            $table->string('author_name');
            $table->string('author_title')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_url')->nullable();
            $table->string('avatar_path')->nullable();
            $table->boolean('featured')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['wall_id', 'status', 'featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('testimonials');
    }
};
