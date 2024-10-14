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
            [
                'image_public_id' => 'wedding-photographer',
                'image_url' => 'https://res.cloudinary.com/dztfnnvb4/image/upload/v1728913988/creative_categories/wedding-photographer.jpg',
                'creative_category' => 'Wedding Photographer'
            ],
            [
                'image_public_id' => 'event-photographer',
                'image_url' => 'https://res.cloudinary.com/dztfnnvb4/image/upload/v1728914084/creative_categories/event-photographer.jpg',
                'creative_category' => 'Event Photographer'
            ],
            ['creative_category' => 'Portrait Photographer'],
            [
                'image_public_id' => 'product-photographer',
                'image_url' => 'https://res.cloudinary.com/dztfnnvb4/image/upload/v1728914196/creative_categories/product-photographer.jpg',
                'creative_category' => 'Product Photographer',

            ],
            ['creative_category' => 'Documentary Filmmaking'],
            [
                'image_public_id' => 'nature-wildlife-photographer',
                'image_url' => 'https://res.cloudinary.com/dztfnnvb4/image/upload/v1728913519/creative_categories/nature-wildlife-photographer.jpg',
                'creative_category' => 'Nature & Wildlife Photographer'
            ]
        ];

        foreach ($categories as $category) {
            CreativeCategory::create($category);
        }
    }
}
