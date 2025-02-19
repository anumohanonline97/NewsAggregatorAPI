<?php
namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\UserPreference; 

class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class; 

    public function definition()
    {
        return [
            'user_id' => User::factory(), 
            'category' => $this->faker->word,
            'source' => $this->faker->company,
            'author' => $this->faker->name,
        ];
    }
}

