<?php

namespace Database\Seeders;

use App\Models\CreativeCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreativeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['creative_category' => 'Wedding Photography'],
            ['creative_category' => 'Event Photography'],
            ['creative_category' => 'Portrait Photography'],
            ['creative_category' => 'Product Photography'],
            ['creative_category' => 'Documentary Filmmaking'],
            ['creative_category' => 'Nature & Wildlife Photography']
        ];

        foreach ($categories as $category) {
            CreativeCategory::create($category);
        }
    }
}
