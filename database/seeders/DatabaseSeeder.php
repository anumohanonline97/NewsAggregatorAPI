<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Hash;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'),
            'phone' => '9895198981',
            'address' => 'test,street,town,testpincode',
            'user_type' => 'admin'
        ]);

        User::create([
            'first_name' => 'Edverd',
            'last_name' => 'John',
            'email' => 'user@example.com',
            'password' => Hash::make('user123'),
            'phone' => '9667765565',
            'address' => 'test,street,town,testpincode',
            'user_type' => 'user'
        ]);

        Article::create([
            'title' => 'State Department had a plan to buy $400M worth of armored Tesla vehicles from Elon Musk',
            'author' => 'mlive.com',
            'source' => 'Biztoc.com',
            'category' => 'business',
            'description' => 'By ADRIANA GOMEZ LICON Associated Press\nFORT LAUDERDALE, Fla. (AP) — The State Department had been in talks with Elon Musk’s Tesla company to buy armored electric vehicles, ',
            'url' => 'https://abcdsadasd',
        ]);
        Article::create([
            'title' => "Tesla stock finds support, for now",
            'author' => "aol.com",
            'source' => "Biztoc.com",
            'category' => 'business',
            'description' => "Tesla's (TSLA) beat-up stock has found support on the charts, for now.\nAfter a brutal stretch this month that brought the stock's year-to-date decline to more than 30% at one point",
            'url' => "https://abcdsadasd",
        ]);

        UserPreference::create([
            'user_id' => "2",
            'category' => "business",
            'source' => "CNN",
            'author' => "aol.com",
        ]);


    }
}
