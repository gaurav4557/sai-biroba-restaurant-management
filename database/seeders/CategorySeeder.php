<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Soups & Shorba',
                'slug' => 'soups-shorba',
                'description' => 'Aromatic traditional Indian and continental soups',
                'display_order' => 1,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Starters & Appetizers',
                'slug' => 'starters-appetizers',
                'description' => 'Crispy and savory starters to begin your dining experience',
                'display_order' => 2,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Tandoor & Kebabs',
                'slug' => 'tandoor-kebabs',
                'description' => 'Charcoal-grilled delicacies prepared in traditional clay oven',
                'display_order' => 3,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Main Course',
                'slug' => 'main-course',
                'description' => 'Rich, authentic regional curries and gravies',
                'display_order' => 4,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Indian Breads',
                'slug' => 'indian-breads',
                'description' => 'Freshly baked rotis, naans, and kulchas from the tandoor',
                'display_order' => 5,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Rice & Biryani',
                'slug' => 'rice-biryani',
                'description' => 'Fragrant basmati rice preparations and authentic dum biryanis',
                'display_order' => 6,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Desserts & Sweets',
                'slug' => 'desserts-sweets',
                'description' => 'Classic Indian sweets and decadent dessert delicacies',
                'display_order' => 7,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Beverages',
                'slug' => 'beverages',
                'description' => 'Refreshing lassis, mocktails, fresh juices and hot beverages',
                'display_order' => 8,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($categories as $category) {
            DB::table('categories')->updateOrInsert(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}
