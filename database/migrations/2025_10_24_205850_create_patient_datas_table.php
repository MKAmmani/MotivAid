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
        Schema::create('patient_datas', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('age');
            $table->string('history_of_antenatal_visit');
            $table->string('gravida');
            $table->string('history_of_previous_pph');
            $table->string('history_Of_ceaserian_section');
            $table->string('type_of_pregenency');
            $table->string('gestational_age');
            $table->string('hospital');
            $table->boolean('is_complete')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_datas');
    }
};
