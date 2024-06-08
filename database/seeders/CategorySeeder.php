<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Electronics',
            ],
            [
                'name' => 'Books',
            ],
            [
                'name' => 'Clothing',
            ],
            [
                'name' => 'Sports Equipment',
            ],
        ];
        DB::table('categories')->insert($categories);
    }
}