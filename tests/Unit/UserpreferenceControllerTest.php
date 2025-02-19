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
     public function test_authenticated_user_with_preferences_can_retrieve_them()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $preferences = UserPreference::factory()->count(3)->create([
            'user_id' => $user->id, 
        ]);

        $response = $this->getJson('/api/getuserpreferencesnews');

        $response->assertStatus(200)
         ->assertJsonStructure([
             'message',
             'articles' => [
                 'current_page',
                 'data' => [
                     '*' => [
                         'id', 'title', 'author', 'source', 'category', 'description', 'url', 'published_at'
                     ],
                 ],
                 'first_page_url',
                 'from',
                 'last_page',
                 'last_page_url',
                 'next_page_url',
                 'path',
                 'per_page',
                 'prev_page_url',
                 'to',
                 'total',
             ]
         ]);

    }

   
     /** @test */
     public function test_guest_cannot_fetch_preferences()
     {
         $response = $this->getJson('/api/getuserpreferencesnews');
 
         $response->assertStatus(401);
     }
}
