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
            [
                'image_public_id' => 'Nature',
                'image_url' => 'https://res.cloudinary.com/dztfnnvb4/image/upload/v1728920432/photo_categories/Nature.jpg',
                'photo_category' => 'Nature'
            ],
            ['photo_category' => 'Portrait'],
            ['photo_category' => 'Architecture'],
            ['photo_category' => 'Wildlife'],
            [
                'image_public_id' => 'Fashion',
                'image_url' => 'https://res.cloudinary.com/dztfnnvb4/image/upload/v1728920789/photo_categories/Fashion.jpg',
                'photo_category' => 'Fashion'
            ],
            ['photo_category' => 'Food'],
        ];

        foreach ($categories as $category) {
            PhotoCategory::create($category);
        }
    }
}
