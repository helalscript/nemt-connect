<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AppLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('app_languages')->insert([
            ['name' => 'English', 'code' => 'en', 'flag_icon' => 'uploads/language-flags/en.png', 'is_default' => true],
            ['name' => 'Russian', 'code' => 'ru', 'flag_icon' => 'uploads/language-flags/ru.png', 'is_default' => false],
            ['name' => 'Uzbek', 'code' => 'uz', 'flag_icon' => 'uploads/language-flags/uz.png', 'is_default' => false],
        ]);
    }
}
