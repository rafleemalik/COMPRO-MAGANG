<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PostgreSQL-only: SQLite does not support ALTER TABLE ... DROP CONSTRAINT / ADD CONSTRAINT
     * the same way; Laravel enum on SQLite uses a different mechanism.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE internship_applications DROP CONSTRAINT IF EXISTS internship_applications_status_check');

        DB::statement("ALTER TABLE internship_applications ADD CONSTRAINT internship_applications_status_check CHECK (status IN ('pending', 'approved', 'rejected', 'finished'))");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('ALTER TABLE internship_applications DROP CONSTRAINT IF EXISTS internship_applications_status_check');

        DB::statement("ALTER TABLE internship_applications ADD CONSTRAINT internship_applications_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
    }
};
