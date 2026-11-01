<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = DB::table('categories')->pluck('id', 'slug');

        $items = [
            // Soups & Shorba
            [
                'category_slug' => 'soups-shorba',
                'item_code' => 'SOU-01',
                'name' => 'Tomato Dhaniya Shorba',
                'description' => 'A traditional Indian thin soup with ripe tomatoes, fresh coriander and mild spices.',
                'price' => 140.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 10,
            ],
            [
                'category_slug' => 'soups-shorba',
                'item_code' => 'SOU-02',
                'name' => 'Murgh Yakhni Shorba',
                'description' => 'Slow-simmered chicken broth infused with whole spices and fresh herbs.',
                'price' => 180.00,
                'dietary_type' => 'non_veg',
                'spice_level' => 2,
                'is_available' => true,
                'prep_time_minutes' => 12,
            ],

            // Starters & Appetizers
            [
                'category_slug' => 'starters-appetizers',
                'item_code' => 'STA-01',
                'name' => 'Paneer Kurkure',
                'description' => 'Crispy fried cottage cheese strips tossed with spiced herbs and mint dip.',
                'price' => 260.00,
                'dietary_type' => 'veg',
                'spice_level' => 2,
                'is_available' => true,
                'prep_time_minutes' => 15,
            ],
            [
                'category_slug' => 'starters-appetizers',
                'item_code' => 'STA-02',
                'name' => 'Egg Pepper Fry',
                'description' => 'Hard-boiled eggs tossed in a pungent crushed black pepper and caramelized onion masala.',
                'price' => 190.00,
                'dietary_type' => 'egg',
                'spice_level' => 3,
                'is_available' => true,
                'prep_time_minutes' => 12,
            ],
            [
                'category_slug' => 'starters-appetizers',
                'item_code' => 'STA-03',
                'name' => 'Chicken Lollipop Masala Dry',
                'description' => 'Crisp fried chicken wings tossed in a spicy, tangy Indo-Chinese garlic sauce.',
                'price' => 310.00,
                'dietary_type' => 'non_veg',
                'spice_level' => 3,
                'is_available' => true,
                'prep_time_minutes' => 18,
            ],

            // Tandoor & Kebabs
            [
                'category_slug' => 'tandoor-kebabs',
                'item_code' => 'TAN-01',
                'name' => 'Tandoori Paneer Tikka',
                'description' => 'Fresh cottage cheese cubes marinated in spiced yogurt and grilled in traditional clay tandoor.',
                'price' => 280.00,
                'dietary_type' => 'veg',
                'spice_level' => 2,
                'is_available' => true,
                'prep_time_minutes' => 20,
            ],
            [
                'category_slug' => 'tandoor-kebabs',
                'item_code' => 'TAN-02',
                'name' => 'Murgh Malai Tikka',
                'description' => 'Boneless tender chicken chunks marinated in rich cream, cashew paste, and mild aromatic cardamom.',
                'price' => 340.00,
                'dietary_type' => 'non_veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 20,
            ],
            [
                'category_slug' => 'tandoor-kebabs',
                'item_code' => 'TAN-03',
                'name' => 'Tandoori Chicken (Half)',
                'description' => 'Whole chicken leg & breast marinated overnight in red chili, mustard oil, and yogurt.',
                'price' => 360.00,
                'dietary_type' => 'non_veg',
                'spice_level' => 2,
                'is_available' => true,
                'prep_time_minutes' => 25,
            ],

            // Main Course
            [
                'category_slug' => 'main-course',
                'item_code' => 'MAI-01',
                'name' => 'Paneer Butter Masala',
                'description' => 'Soft cottage cheese cubes cooked in a rich, buttery, velvety tomato gravy with kasoori methi.',
                'price' => 320.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 18,
            ],
            [
                'category_slug' => 'main-course',
                'item_code' => 'MAI-02',
                'name' => 'Veg Kolhapuri',
                'description' => 'Assorted farm-fresh vegetables cooked in a fiery, spicy Maharashtrian coconut-based gravy.',
                'price' => 290.00,
                'dietary_type' => 'veg',
                'spice_level' => 3,
                'is_available' => true,
                'prep_time_minutes' => 18,
            ],
            [
                'category_slug' => 'main-course',
                'item_code' => 'MAI-03',
                'name' => 'Egg Curry Dhaba Style',
                'description' => 'Golden fried eggs simmered in a robust, rustic onion-tomato dhaba gravy.',
                'price' => 240.00,
                'dietary_type' => 'egg',
                'spice_level' => 2,
                'is_available' => true,
                'prep_time_minutes' => 15,
            ],
            [
                'category_slug' => 'main-course',
                'item_code' => 'MAI-04',
                'name' => 'Butter Chicken Special',
                'description' => 'Tandoor-roasted chicken pieces cooked in our signature silky tomato butter cream reduction.',
                'price' => 380.00,
                'dietary_type' => 'non_veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 20,
            ],
            [
                'category_slug' => 'main-course',
                'item_code' => 'MAI-05',
                'name' => 'Chicken Sukka (Saoji Style)',
                'description' => 'Dry roasted tender chicken with toasted coconut, stone flower and Vidarbha spicy masala.',
                'price' => 360.00,
                'dietary_type' => 'non_veg',
                'spice_level' => 3,
                'is_available' => true,
                'prep_time_minutes' => 20,
            ],

            // Indian Breads
            [
                'category_slug' => 'indian-breads',
                'item_code' => 'BRE-01',
                'name' => 'Butter Roti',
                'description' => 'Whole wheat tandoori flatbread brushed with golden butter.',
                'price' => 35.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 8,
            ],
            [
                'category_slug' => 'indian-breads',
                'item_code' => 'BRE-02',
                'name' => 'Butter Garlic Naan',
                'description' => 'Refined flour leavened bread layered with minced garlic, coriander, and fresh melted butter.',
                'price' => 70.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 10,
            ],

            // Rice & Biryani
            [
                'category_slug' => 'rice-biryani',
                'item_code' => 'RIC-01',
                'name' => 'Jeera Rice with Dal Tadka',
                'description' => 'Fragrant cumin basmati rice accompanied by home-style yellow lentil tempered with garlic & ghee.',
                'price' => 220.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 15,
            ],
            [
                'category_slug' => 'rice-biryani',
                'item_code' => 'RIC-02',
                'name' => 'Chicken Dum Biryani',
                'description' => 'Aged long-grain basmati rice and marinated chicken cooked on slow dum with saffron and kewra.',
                'price' => 360.00,
                'dietary_type' => 'non_veg',
                'spice_level' => 2,
                'is_available' => true,
                'prep_time_minutes' => 20,
            ],

            // Desserts & Sweets
            [
                'category_slug' => 'desserts-sweets',
                'item_code' => 'DES-01',
                'name' => 'Gulab Jamun with Rabdi',
                'description' => 'Warm khoya dumplings soaked in rose syrup served atop chilled thickened sweet rabdi.',
                'price' => 130.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 5,
            ],

            // Beverages
            [
                'category_slug' => 'beverages',
                'item_code' => 'BEV-01',
                'name' => 'Royal Masala Buttermilk (Chaas)',
                'description' => 'Chilled churned curd spiced with roasted cumin, rock salt, ginger, and green chillies.',
                'price' => 70.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 5,
            ],
            [
                'category_slug' => 'beverages',
                'item_code' => 'BEV-02',
                'name' => 'Mango Sweet Lassi',
                'description' => 'Thick creamy yogurt whipped with Alphonso mango pulp and cardamom.',
                'price' => 110.00,
                'dietary_type' => 'veg',
                'spice_level' => 1,
                'is_available' => true,
                'prep_time_minutes' => 5,
            ],
        ];

        foreach ($items as $item) {
            $categorySlug = $item['category_slug'];
            unset($item['category_slug']);

            if (isset($categories[$categorySlug])) {
                $item['category_id'] = $categories[$categorySlug];
                $item['created_at'] = now();
                $item['updated_at'] = now();

                DB::table('menu_items')->updateOrInsert(
                    ['item_code' => $item['item_code']],
                    $item
                );
            }
        }
    }
}
