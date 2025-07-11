<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_logs', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->enum('type', ['website_visit', 'keystroke', 'activity']);
            $table->string('url')->nullable();
            $table->string('title')->nullable();
            $table->string('domain')->nullable();
            $table->longText('content')->nullable(); // untuk keystroke content
            $table->json('activity_data')->nullable(); // untuk activity data
            $table->json('form_data')->nullable(); // untuk form submission data
            $table->timestamp('logged_at');
            $table->timestamps();
            
            $table->index(['employee_id', 'type']);
            $table->index(['employee_id', 'logged_at']);
            $table->index('domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_logs');
    }
};