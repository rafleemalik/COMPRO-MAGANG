<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\InternshipApplication;
use App\Models\Divisi;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call seeders to populate data
        $this->call([
            // DirektoratSeeder::class, // Disabled - using DivisiSeeder instead
            FieldOfInterestSeeder::class,
            DivisiSeeder::class,
            MentorUserSeeder::class,
        ]);

        // Create default admin user
        User::create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@telkomindonesia.co.id',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
        ]);

        // Create mentor (pembimbing) user for each divisi
        foreach (Divisi::all() as $divisi) {
            User::create([
                'username' => 'mentor_' . Str::slug($divisi->name, '_'),
                'name' => 'Pembimbing ' . $divisi->name,
                'email' => 'mentor_' . Str::slug($divisi->name, '_') . '@telkomindonesia.co.id',
                'password' => Hash::make('mentor123'),
                'role' => 'pembimbing',
                'divisi_id' => $divisi->id,
            ]);
        }

        // Seeder peraturan default jika belum ada
        if (\App\Models\Rule::count() == 0) {
            \App\Models\Rule::create(['content' =>
                "1. Peserta wajib mematuhi seluruh peraturan dan tata tertib yang berlaku di PT Pos Indonesia.\n".
                "2. Peserta dilarang melakukan tindakan yang dapat merugikan perusahaan, baik secara langsung maupun tidak langsung.\n".
                "3. Peserta wajib menjaga kerahasiaan data dan informasi perusahaan.\n".
                "4. Peserta wajib hadir dan mengikuti seluruh kegiatan magang sesuai jadwal yang telah ditentukan.\n".
                "5. Peserta wajib menjaga sikap, perilaku, dan sopan santun selama berada di lingkungan perusahaan.\n".
                "6. Peserta dilarang menyalahgunakan fasilitas perusahaan untuk kepentingan pribadi.\n".
                "7. Peserta wajib melaporkan setiap kendala atau permasalahan kepada pembimbing/mentor magang.\n".
                "8. Peraturan dapat berubah sewaktu-waktu sesuai kebijakan perusahaan."
            ]);
        }
    }
}
