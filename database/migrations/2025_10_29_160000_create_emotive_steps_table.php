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
        Schema::create('emotive_steps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('patient_data_id');
            $table->string('action');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            // Foreign key constraint
            $table->foreign('patient_data_id')->references('id')->on('patient_datas')->onDelete('cascade');
            
            // Index for better query performance
            $table->index(['patient_data_id', 'completed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emotive_steps');
    }
};