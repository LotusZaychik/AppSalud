<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'location')) {
                $table->string('location')->nullable()->after('date');
            }
            if (!Schema::hasColumn('appointments', 'reason')) {
                $table->string('reason')->nullable()->after('location');
            }
            if (!Schema::hasColumn('appointments', 'status')) {
                $table->string('status')->nullable()->after('reason');
            }
            if (!Schema::hasColumn('appointments', 'doctor_name')) {
                $table->string('doctor_name')->nullable()->after('status');
            }
            if (!Schema::hasColumn('appointments', 'notes')) {
                $table->string('notes')->nullable()->after('doctor_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['location', 'reason', 'status', 'doctor_name', 'notes']);
        });
    }
};
