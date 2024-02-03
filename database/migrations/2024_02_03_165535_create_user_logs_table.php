<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('user_name')->nullable();
            $table->string('parent');
            $table->string('parent_id');
            $table->string('action');
            $table->dateTime('action_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->boolean('status');
            $table->string('ip_address');
            $table->string('browser');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_logs');
    }
};
