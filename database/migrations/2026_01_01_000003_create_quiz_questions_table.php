<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('model_id')->constrained('models_3d')->cascadeOnDelete();
            $table->text('question_text');
            $table->string('option_a', 255)->nullable();
            $table->string('option_b', 255)->nullable();
            $table->string('option_c', 255)->nullable();
            $table->string('option_d', 255)->nullable();
            $table->char('correct_option', 1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
