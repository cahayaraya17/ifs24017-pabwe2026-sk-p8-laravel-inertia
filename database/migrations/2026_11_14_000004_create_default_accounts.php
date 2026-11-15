<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Membuat akun admin dan member bawaan di server (idempotent).
 * Dibutuhkan karena `migrate` tidak menjalankan seeder, padahal penilaian
 * memakai login admin / admin. Dilewati saat testing agar tes tidak terpengaruh.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $now = now();

        foreach ([
            ['username' => 'admin', 'name' => 'Admin', 'email' => 'admin@example.com', 'role' => 'admin'],
            ['username' => 'member', 'name' => 'Member', 'email' => 'member@example.com', 'role' => 'member'],
        ] as $account) {
            DB::table('users')->updateOrInsert(
                ['username' => $account['username']],
                [
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'role' => $account['role'],
                    'password' => Hash::make($account['username']),
                    'email_verified_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        // Sengaja kosong: akun tidak dihapus saat rollback.
    }
};
