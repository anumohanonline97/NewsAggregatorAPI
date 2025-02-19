<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Controllers\AuthController;
use App\Jobs\FetchArticlesFromGuardian;
use App\Jobs\FetchArticlesFromNewsAPI;
use App\Jobs\FetchArticlesFromNYT;
use App\Models\Article;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;

class UserpreferenceControllerTest extends TestCase
{
    /**
     * Testing for saving user preferences
     */
    /** @test */
    public function test_authenticated_user_can_save_preferences()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/saveuserpreferences', [
            'category' => 'Technology',
            'source' => 'BBC News',
            'author' => 'John Doe',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'message' => 'User preferences saved successfully!',
                     'preference' => [
                         'category' => 'Technology',
                         'source' => 'BBC News',
                         'author' => 'John Doe',
                     ],
                 ]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'category' => 'Technology',
            'source' => 'BBC News',
            'author' => 'John Doe',
        ]);
    }

    /** @test */
    public function test_guest_cannot_save_preferences()
    {
        $response = $this->postJson('/api/saveuserpreferences', [
            'category' => 'Health',
            'source' => 'CNN',
            'author' => 'Jane Doe',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function test_validation_errors_when_saving_preferences()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/saveuserpreferences', [
            'category' => 123,  
            'source' => null,  
            'author' => ['Jane Doe'], 
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['category', 'author']);
    }

    /**Testing for fetching user preferred news */
     /** @test */
     public function test_authenticated_user_with_preferences_receives_filtered_articles()
     {
         $user = User::factory()->create();
 
         Sanctum::actingAs($user);
 
         UserPreference::create([
             'user_id' => $user->id,
             'category' => 'Technology',
             'source' => 'BBC News',
             'author' => 'John Doe',
         ]);
 
         Article::factory()->create(['category' => 'Technology', 'source' => 'BBC News', 'author' => 'John Doe']);
         Article::factory()->create(['category' => 'Health', 'source' => 'CNN', 'author' => 'Jane Doe']);
 
         $response = $this->getJson('/api/getuserpreferencesnews');
 
         $response->assertStatus(200)
                  ->assertJson([
                      'message' => 'Preferred news articles retrieved successfully!',
                  ]);
 
         $response->assertJsonPath('articles.data.0.category', 'Technology');
         $response->assertJsonPath('articles.data.0.source', 'BBC News');
         $response->assertJsonPath('articles.data.0.author', 'John Doe');
     }
 
     /** @test */
     public function test_authenticated_user_without_preferences_receives_all_articles()
     {
         $user = User::factory()->create();
 
         Sanctum::actingAs($user);
 
         Article::factory()->count(5)->create();
 
         $response = $this->getJson('/api/getuserpreferencesnews');
 
         $response->assertStatus(200)
                  ->assertJson([
                      'message' => 'No preferences found, returning all articles.',
                  ]);
 
         $this->assertCount(5, $response->json('articles.data'));
     }
 
     /** @test */
     public function test_guest_cannot_fetch_preferences()
     {
         $response = $this->getJson('/api/getuserpreferencesnews');
 
         $response->assertStatus(401);
     }
}
