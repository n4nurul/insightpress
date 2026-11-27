<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@websinsight.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password123')]
        );

        $cats = ['জাতীয়', 'সিলেট', 'বিশ্বনাথ', 'রাজনীতি', 'খেলাধুলা', 'প্রবাস'];
        foreach ($cats as $i => $name) {
            $cat = Category::firstOrCreate(
                ['slug' => bn_slug($name)],
                ['name' => $name, 'type' => 'news', 'sort_order' => $i + 1, 'show_in_menu' => true, 'is_active' => true]
            );

            Post::firstOrCreate(
                ['slug' => bn_slug($name . '-প্রধান-সংবাদ')],
                [
                    'user_id' => $admin->id,
                    'category_id' => $cat->id,
                    'type' => 'news',
                    'sub_title' => $name . ' বিশেষ প্রতিবেদন',
                    'title' => $name . ' অঞ্চলে উন্নয়ন ও জনজীবনের সর্বশেষ খবরাখবর',
                    'excerpt' => 'স্থানীয় প্রশাসন ও সাধারণ মানুষের অংশগ্রহণে এগিয়ে চলছে উন্নয়নমূলক কার্যক্রম।',
                    'body' => "স্থানীয় প্রশাসন ও সাধারণ মানুষের অংশগ্রহণে এগিয়ে চলছে উন্নয়নমূলক কার্যক্রম। আমাদের নিজস্ব প্রতিবেদক জানান, এলাকার সার্বিক পরিস্থিতি স্বাভাবিক রয়েছে এবং নতুন প্রকল্পগুলো দ্রুত বাস্তবায়িত হচ্ছে।\n\nএলাকাবাসী এই উদ্যোগকে স্বাগত জানিয়েছেন।",
                    'is_breaking' => $i < 3,
                    'is_featured' => $i < 5,
                    'seo_score' => 85,
                    'status' => 'published',
                    'published_at' => now()->subMinutes($i * 15),
                    'meta_data' => ['reporter' => 'নিজস্ব প্রতিবেদক'],
                ]
            );
        }

        Post::firstOrCreate(
            ['slug' => 'editor-in-chief'],
            [
                'type' => 'team',
                'title' => 'মোহাম্মদ নূরুল ইসলাম',
                'status' => 'published',
                'meta_data' => ['designation' => 'সম্পাদক ও প্রকাশক', 'area' => 'বিশ্বনাথ, সিলেট', 'phone' => '+8801700000000'],
            ]
        );
    }
}
