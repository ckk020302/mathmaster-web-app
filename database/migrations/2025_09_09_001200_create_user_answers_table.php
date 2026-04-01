<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');

            $table->string('academic_level', 50)->nullable();
            $table->string('chapter', 255)->nullable();
            $table->string('difficulty', 50)->nullable();

            $table->json('question_pool')->nullable();
            $table->json('answers')->nullable();
            $table->unsignedInteger('current_index')->default(0);
            $table->string('status', 20)->default('in-progress');
            $table->integer('score')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_answers');
    }
};

