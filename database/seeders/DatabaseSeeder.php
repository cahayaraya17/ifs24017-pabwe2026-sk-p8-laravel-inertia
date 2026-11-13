<?php

namespace Database\Seeders;

use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@example.com',
            'password' => 'admin',
            'role' => 'admin',
        ]);

        $member = User::factory()->create([
            'name' => 'Member',
            'username' => 'member',
            'email' => 'member@example.com',
            'password' => 'member',
            'role' => 'member',
        ]);

        Post::factory(8)->create(['user_id' => $member->id]);
        Post::factory(2)->draft()->create(['user_id' => $member->id]);
    }
}