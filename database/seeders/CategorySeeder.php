<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Вечеринка дома',
                'slug' => 'house-party',
            ],
            [
                'name' => 'Клуб',
                'slug' => 'club',
            ],
            [
                'name' => 'Бар',
                'slug' => 'bar',
            ],
            [
                'name' => 'Настольные игры',
                'slug' => 'board-games',
            ],
            [
                'name' => 'Природа',
                'slug' => 'outdoors',
            ],
            [
                'name' => 'Искусство',
                'slug' => 'art',
            ],
            [
                'name' => 'Киновечер',
                'slug' => 'movie-night',
            ],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                ['slug' => $category['slug']],
                [
                    'name' => $category['name'],
                    'is_active' => true,
                ],
            );
        }
    }
}
