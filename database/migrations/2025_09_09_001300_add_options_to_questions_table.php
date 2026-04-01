<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('academic_level', 50);
            $table->string('chapter', 200);
            $table->enum('difficulty', ['Easy', 'Intermediate', 'Advanced']);
            $table->string('question_image')->nullable();   // store image path
            $table->string('answer_image', 1)->nullable();  // store option A/B/C/D
            
            // Updated tip columns (removed _image suffix)
            $table->string('tip_easy')->nullable();
            $table->string('tip_intermediate')->nullable();
            $table->string('tip_advanced')->nullable();
            
            $table->string('uploaded_by')->nullable();
            $table->unsignedBigInteger('user_id')->default(1);
            $table->timestamp('upload_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};