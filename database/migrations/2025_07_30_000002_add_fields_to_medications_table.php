<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->string('type')->nullable()->after('notes');
            $table->string('category')->nullable()->after('type');
            $table->integer('duration_days')->nullable()->after('reminder_times');
        });
    }

    public function down(): void
    {
        Schema::table('medications', function (Blueprint $table) {
            $table->dropColumn(['type', 'category', 'duration_days']);
        });
    }
};
