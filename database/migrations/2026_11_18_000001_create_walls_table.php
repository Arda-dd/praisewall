<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('walls', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('headline');
            $table->text('prompt');
            $table->string('accent_color', 7)->default('#6d28d9');
            $table->string('logo_path')->nullable();
            $table->text('thank_you_message');
            $table->boolean('collecting')->default(true);
            $table->boolean('ask_for_rating')->default(true);
            $table->boolean('ask_for_avatar')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('walls');
    }
};
