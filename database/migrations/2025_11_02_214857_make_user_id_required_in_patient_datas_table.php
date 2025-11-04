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
        // Note: SQLite doesn't support changing column properties easily
        // It's better to keep user_id nullable and handle the requirement in application logic
        // since new records will automatically get a user_id from auth()->id()
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
