<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ubah tipe kolom status ke VARCHAR(255)
        DB::statement('ALTER TABLE pesanans ALTER COLUMN status TYPE VARCHAR(255);');
        // Set default value
        DB::statement("ALTER TABLE pesanans ALTER COLUMN status SET DEFAULT 'pending';");
        // Set not null
        DB::statement("ALTER TABLE pesanans ALTER COLUMN status SET NOT NULL;");
        // Hapus constraint check lama (jika ada)
        DB::statement("ALTER TABLE pesanans DROP CONSTRAINT IF EXISTS pesanans_status_check;");
        // Tambahkan constraint check baru
        DB::statement("ALTER TABLE pesanans ADD CONSTRAINT pesanans_status_check CHECK (status IN ('pending', 'processing', 'dikirim', 'completed', 'cancelled'));");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus constraint check
        DB::statement("ALTER TABLE pesanans DROP CONSTRAINT IF EXISTS pesanans_status_check;");
        // Kembalikan ke tipe string tanpa check constraint
        DB::statement('ALTER TABLE pesanans ALTER COLUMN status TYPE VARCHAR(255);');
        DB::statement("ALTER TABLE pesanans ALTER COLUMN status SET DEFAULT 'pending';");
        DB::statement("ALTER TABLE pesanans ALTER COLUMN status SET NOT NULL;");
    }
}; 