<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Queue;
use App\Jobs\FetchArticlesFromGuardian;
use App\Jobs\FetchArticlesFromNewsAPI;
use App\Jobs\FetchArticlesFromNYT;

class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_api_user_can_save_article_with_token()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 
        $response = $this->postJson('/api/articles', [
            'title' => 'Tesla stock surges',
            'author' => 'John Doe',
            'source' => 'Business Insider',
            'category' => 'Business',
            'description' => 'Tesla stock reaches all-time high amid market optimism.',
            'url' => 'https://example.com/tesla-stock',
            'published_at' => now()->toDateString(),
        ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Article saved successfully!']);

        $this->assertDatabaseHas('articles', ['title' => 'Tesla stock surges']);
    }

    /** @test */
    public function test_api_user_cannot_save_article_with_missing_fields()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $response = $this->postJson('/api/articles', []); 

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function test_api_user_cannot_save_article_with_invalid_date()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $response = $this->postJson('/api/articles', [
            'title' => 'Invalid Date Article',
            'author' => 'Jane Doe',
            'source' => 'CNN',
            'category' => 'Tech',
            'description' => 'A test article with an invalid date.',
            'url' => 'https://example.com/invalid-date',
            'published_at' => 'invalid-date',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['published_at']);
    }

    /** @test */
    public function test_api_user_can_update_article()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $article = Article::factory()->create([
            'title' => 'Old Title',
            'author' => 'John Doe',
            'source' => 'TechCrunch',
            'category' => 'Tech',
            'description' => 'Old description.',
            'url' => 'https://example.com/old',
            'published_at' => now()->toDateString(),
        ]);

        $response = $this->putJson("/api/articles/{$article->id}", [
            'title' => 'Updated Title',
            'author' => 'Jane Doe',
            'source' => 'CNN',
            'category' => 'Business',
            'description' => 'Updated description.',
            'url' => 'https://example.com/updated',
            'published_at' => now()->toDateString(),
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Article updated successfully!']);

        $this->assertDatabaseHas('articles', ['title' => 'Updated Title']);
    }

    /** @test */
    public function test_api_user_can_delete_an_article()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user); 

        $article = Article::factory()->create();

        $response = $this->deleteJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Article deleted successfully!']);

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
    }

    /** @test */
    public function test_api_can_list_articles()
    {
        $user = User::factory()->create();
    
        Sanctum::actingAs($user); 
    
        Article::factory()->count(3)->create();
    
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken, 
            'Accept' => 'application/json',
        ])->getJson('/api/articles');
    
        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'message',
                     'articles' => [
                         'current_page',
                         'data',
                         'first_page_url',
                         'last_page',
                         'last_page_url',
                         'per_page',
                         'total'
                     ]
                 ]);
    }
    

    /** @test */
    public function test_api_dispatches_fetch_articles_jobs()
    {
        Queue::fake();

        $response = $this->getJson('/api/scheduler');

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Job has been dispatched!']);

        Queue::assertPushed(FetchArticlesFromNewsAPI::class);
        Queue::assertPushed(FetchArticlesFromNYT::class);
        Queue::assertPushed(FetchArticlesFromGuardian::class);
    }

    /** @test */
    public function test_api_can_retrieve_an_article()
{
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $article = Article::factory()->create([
        'title' => 'Sample Article',
        'author' => 'John Doe',
        'source' => 'TechCrunch',
        'category' => 'Technology',
        'description' => 'A sample article description.',
        'url' => 'https://example.com/sample-article',
        'published_at' => '2025-02-19 00:00:00',
    ]);

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken, 
        'Accept' => 'application/json',
    ])->getJson("/api/articles/{$article->id}");

    $response->assertStatus(200)
        ->assertJsonPath('message', 'Article retrieved successfully!')
        ->assertJsonPath('articles.id', $article->id)
        ->assertJsonPath('articles.title', 'Sample Article')
        ->assertJsonPath('articles.author', 'John Doe')
        ->assertJsonPath('articles.source', 'TechCrunch')
        ->assertJsonPath('articles.category', 'Technology')
        ->assertJsonPath('articles.description', 'A sample article description.')
        ->assertJsonPath('articles.url', 'https://example.com/sample-article')
        ->assertJsonPath('articles.published_at', $article->published_at); 
}



    /** @test */
    public function test_api_returns_404_for_non_existent_article()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);
    
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $user->createToken('TestToken')->plainTextToken,
            'Accept' => 'application/json',
        ])->getJson('/api/articles/9999'); 
    
        $response->assertStatus(404)
                 ->assertJson(['message' => 'Article not found']);
    }
    
}
