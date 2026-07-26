<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed initial Admin User
        User::updateOrCreate(
            ['email' => 'admin@dreampc.com'],
            [
                'name' => 'DreamPC Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // Seed Default Hardware Categories
        $categories = [
            'CPU' => 'cpu',
            'GPU' => 'gpu',
            'RAM' => 'ram',
            'Motherboard' => 'motherboard',
            'Storage' => 'storage',
            'PSU' => 'psu',
            'Case' => 'case',
        ];

        foreach ($categories as $name => $slug) {
            Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'parent_id' => null,
                ]
            );
        }
    }
}
