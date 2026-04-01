<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fix PostgreSQL status check constraint to match app statuses.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        // Normalize legacy value before tightening constraint.
        DB::table('internship_applications')
            ->where('status', 'approved')
            ->update(['status' => 'accepted']);

        DB::statement('ALTER TABLE internship_applications DROP CONSTRAINT IF EXISTS internship_applications_status_check');

        DB::statement(
            "ALTER TABLE internship_applications ADD CONSTRAINT internship_applications_status_check CHECK (status IN ('pending', 'accepted', 'rejected', 'finished', 'postponed'))"
        );
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE internship_applications DROP CONSTRAINT IF EXISTS internship_applications_status_check');

        DB::statement(
            "ALTER TABLE internship_applications ADD CONSTRAINT internship_applications_status_check CHECK (status IN ('pending', 'approved', 'rejected', 'finished'))"
        );
    }
};
