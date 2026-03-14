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
        // Kolom trusted device sudah ditambahkan oleh migration
        // 2026_02_18_231513_add_trusted_device_columns_to_users_table.
        // Migration ini dibuat no-op supaya tidak terjadi duplikasi kolom,
        // khususnya pada SQLite yang dipakai di Railway saat boot.
        //
        // Dibiarkan kosong dengan sengaja.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Tidak perlu melakukan apa-apa di sini karena up() sudah no-op.
    }
};
