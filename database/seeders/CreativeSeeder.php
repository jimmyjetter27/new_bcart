<?php

namespace Database\Seeders;

use App\Models\Creative;
use App\Models\Pricing;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreativeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Existing category IDs (replace with actual IDs in your DB)
        $categories = [
            1, // Nature & Wildlife Photographer
            2, // Wedding Photographer
            3, // Event Photographer
            4, // Product Photographer
        ];

        $locations = [
            'Accra',
            'Kumasi',
            'Takoradi',
            'Cape Coast',
            'Tamale',
        ];

        foreach (range(1, 10) as $index) {
            $creative = Creative::create([
                'first_name' => "Creative $index",
                'last_name' => "Lastname $index",
                'username' => "creative$index",
                'email' => "creative$index@example.com",
                'phone_number' => '233' . random_int(200000000, 999999999),
                'city' => $locations[array_rand($locations)],
                'password' => bcrypt('password'),
                'description' => 'A professional photographer',
            ]);

            // Attach random categories from the existing ones in the database
            $creative->creative_categories()->attach(
                array_rand(array_flip($categories), 2) // Select 2 random category IDs
            );

            // Add pricing for each creative
            Pricing::create([
                'creative_id' => $creative->id,
                'hourly_rate' => random_int(100, 500),
                'daily_rate' => random_int(500, 2000),
                'minimum_charge' => random_int(300, 1000),
            ]);
        }
    }
}
