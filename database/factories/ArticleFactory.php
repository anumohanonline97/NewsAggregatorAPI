<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'author' => $this->faker->name,
            'source' => $this->faker->company,
            'category' => $this->faker->word,
            'description' => $this->faker->paragraph,
            'url' => $this->faker->url,
            'published_at' => $this->faker->date,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
