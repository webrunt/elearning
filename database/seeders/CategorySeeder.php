<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Development', 'description' => 'Programming and software engineering'],
            ['name' => 'Business', 'description' => 'Management, marketing, and entrepreneurship'],
            ['name' => 'Design', 'description' => 'UI/UX, graphics, and creative skills'],
        ];

        foreach ($categories as $index => $item) {
            Category::firstOrCreate(
                ['slug' => Str::slug($item['name'])],
                [
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'sort_order' => $index,
                ]
            );
        }
    }
}
