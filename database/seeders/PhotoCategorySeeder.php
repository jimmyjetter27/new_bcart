<?php

namespace Database\Seeders;

use App\Models\PhotoCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PhotoCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['photo_category' => 'Nature'],
            ['photo_category' => 'Portrait'],
            ['photo_category' => 'Architecture'],
            ['photo_category' => 'Wildlife'],
            ['photo_category' => 'Fashion'],
            ['photo_category' => 'Food'],
        ];

        foreach ($categories as $category) {
            PhotoCategory::create($category);
        }
    }
}
