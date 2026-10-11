<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Header fields used by the KPM RPH template: Minggu, Masa, Unit and Tema.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rph_documents', function (Blueprint $table) {
            $table->unsignedTinyInteger('week')->nullable()->after('lesson_date');
            $table->time('start_time')->nullable()->after('week');
            $table->string('theme')->nullable()->after('topic');
            $table->string('unit')->nullable()->after('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rph_documents', function (Blueprint $table) {
            $table->dropColumn(['week', 'start_time', 'theme', 'unit']);
        });
    }
};
