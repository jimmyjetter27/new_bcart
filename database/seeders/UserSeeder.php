<?php

namespace Database\Seeders;

use App\Models\Creative;
use App\Models\RegularUser;
use App\Models\SuperAdmin;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::create([
            'first_name' => 'James',
            'last_name' => 'Atiah',
            'username' => 'jimmyjetter',
            'email' => 'jimmyjetter27@gmail.com',
            'email_verified_at' => now(),
            'type' => SuperAdmin::class,
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
        ]);

//        $user2 = User::create([
//            'first_name' => 'Christian',
//            'last_name' => 'Amevor',
//            'username' => 'jumpingjacks',
//            'email' => 'amevchris@gmail.com',
//            'email_verified_at' => now(),
//            'type' => SuperAdmin::class,
//            'password' => Hash::make('password'),
//            'remember_token' => Str::random(10),
//        ]);

//        $user3 = User::create([
//            'first_name' => 'Neal',
//            'last_name' => 'Brakus',
//            'username' => 'Lone Wolf',
//            'email' => 'jacquelyn79@example.com',
//            'email_verified_at' => now(),
//            'type' => Creative::class,
//            'password' => Hash::make('password'),
//            'remember_token' => Str::random(10),
//        ]);

//        $user4 = User::create([
//            'first_name' => 'Laura',
//            'last_name' => 'Heller',
//            'username' => 'awoken monkey',
//            'email' => 'walton38@example.net',
//            'email_verified_at' => now(),
//            'type' => RegularUser::class,
//            'password' => Hash::make('password'),
//            'remember_token' => Str::random(10),
//        ]);

//        User::factory()->count(6)->create();
    }
}
