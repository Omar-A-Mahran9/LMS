<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name_ar' => 'الصف الاول الثانوي',
                'name_en' => 'First Secondary Grade',
                'description_ar' => 'الصف الاول الثانوي',
                'description_en' => 'First Secondary Grade',
                'parent_id' => null,
            ],
            [
                'name_ar' => 'الصف الثاني الثانوي',
                'name_en' => 'Second Secondary Grade',
                'description_ar' => 'الصف الثاني الثانوي',
                'description_en' => 'Second Secondary Grade',
                'parent_id' => null,
            ],
            [
                'name_ar' => 'الصف الثالث الثانوي',
                'name_en' => 'Third Secondary Grade',
                'description_ar' => 'الصف الثالث الثانوي',
                'description_en' => 'Third Secondary Grade',
                'parent_id' => null,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
