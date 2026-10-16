<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('models_3d')->cascadeOnDelete();
            $table->integer('score');
            $table->integer('total_questions');
            $table->float('duration_seconds');
            $table->timestamp('submitted_at')->useCurrent();
        });

        Schema::create('view_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('model_id')->constrained('models_3d')->cascadeOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->boolean('used_ar')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('view_sessions');
        Schema::dropIfExists('quiz_results');
    }
};
