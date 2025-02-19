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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;


class ArticleControllerTest extends TestCase
{
    use RefreshDatabase;

    /**Testing for save article */
     /** @test */
    public function test_api_user_can_save_article()
    {
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
        $response = $this->postJson('/api/articles', []);

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    /** @test */
    public function test_api_user_cannot_save_article_with_invalid_date()
    {
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

    /**Testing for update article details */
     /** @test */
     public function test_api_user_can_update_article()
     {
         $article = Article::create([
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
     public function test_api_user_cannot_update_non_existing_article()
     {
         $response = $this->putJson('/api/articles/9999', [
             'title' => 'New Title',
             'author' => 'Someone',
             'source' => 'BBC',
             'category' => 'Politics',
             'description' => 'Trying to update non-existing article.',
             'url' => 'https://example.com/new',
             'published_at' => now()->toDateString(),
         ]);
 
         $response->assertStatus(404)
                  ->assertJson(['message' => 'Article not found']);
     }
 
     /** @test */
     public function test_api_user_cannot_update_article_with_invalid_data()
     {
         $article = Article::create([
             'title' => 'Valid Title',
             'author' => 'Jane Doe',
             'source' => 'Forbes',
             'category' => 'Finance',
             'description' => 'Valid description.',
             'url' => 'https://example.com/valid',
             'published_at' => now()->toDateString(),
         ]);
 
         $response = $this->putJson("/api/articles/{$article->id}", [
             'title' => '', 
             'author' => 'Jane Doe',
             'source' => 'Forbes',
             'category' => 'Finance',
             'description' => 'Updated description.',
             'url' => 'https://example.com/updated',
             'published_at' => 'invalid-date', 
         ]);
 
         $response->assertStatus(422)
                  ->assertJsonValidationErrors(['title', 'published_at']);
     }
     /**Test for listing articles */

      /** @test */
    public function test_api_can_list_articles()
    {
        Article::factory()->count(3)->create();

        $response = $this->getJson('/api/articles');

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
    public function test_api_can_list_articles_with_search_filter()
    {
        Article::factory()->create([
            'title' => 'Special Article',
            'description' => 'This is a unique description'
        ]);

        $response = $this->getJson('/api/articles?search=Special');

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Special Article']);
    }

    /** @test */
    public function test_api_can_list_articles_with_source_filter()
    {
        Article::factory()->create([
            'title' => 'Tech Article',
            'source' => 'TechCrunch'
        ]);

        $response = $this->getJson('/api/articles?source=TechCrunch');

        $response->assertStatus(200)
                 ->assertJsonFragment(['source' => 'TechCrunch']);
    }

    /** @test */
    public function test_api_can_list_articles_with_category_filter()
    {
        Article::factory()->create([
            'title' => 'Business News',
            'category' => 'Business'
        ]);

        $response = $this->getJson('/api/articles?category=Business');

        $response->assertStatus(200)
                 ->assertJsonFragment(['category' => 'Business']);
    }

    /** @test */
    public function test_api_can_list_articles_with_date_filter()
    {
        $article = Article::factory()->create([
            'published_at' => '2025-02-18'
        ]);

        $response = $this->getJson('/api/articles?date=2025-02-18');

        $response->assertStatus(200)
                 ->assertJsonFragment(['published_at' => '2025-02-18']);
    }

    /** @test */
    public function test_api_returns_empty_list_when_no_articles_match()
    {
        $response = $this->getJson('/api/articles?search=NonExistentTitle');

        $response->assertStatus(200)
                 ->assertJson(['articles' => [
                     'data' => []
                 ]]);
    }
    /**Test for getting articles */

    /** @test */
    public function test_api_can_retrieve_an_article()
    {
        $article = Article::factory()->create([
            'title' => 'Sample Article',
            'author' => 'John Doe',
            'source' => 'TechCrunch',
            'category' => 'Technology',
            'description' => 'A sample article description.',
            'url' => 'https://example.com/sample-article',
            'published_at' => '2025-02-18',
        ]);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'message' => 'Article retrieved successfully!',
                    'articles' => [
                        'id' => $article->id,
                        'title' => 'Sample Article',
                        'author' => 'John Doe',
                        'source' => 'TechCrunch',
                        'category' => 'Technology',
                        'description' => 'A sample article description.',
                        'url' => 'https://example.com/sample-article',
                        'published_at' => '2025-02-18',
                    ]
                ]);
    }

    /** @test */
    public function test_api_returns_404_for_non_existent_article()
    {
        $response = $this->getJson('/api/articles/9999'); 

        $response->assertStatus(404)
                ->assertJson(['message' => 'Article not found']);
    }

    /**Test for deleting article */

     /** @test */
     public function test_api_can_delete_an_article()
     {
         $article = Article::factory()->create();
 
         $response = $this->deleteJson("/api/articles/{$article->id}");
 
         $response->assertStatus(200)
                  ->assertJson(['message' => 'Article deleted successfully!']);
 
         $this->assertDatabaseMissing('articles', ['id' => $article->id]);
     }
 
     /** @test */
     public function test_api_returns_404_for_non_existent_article_for_delete()
     {
         $response = $this->deleteJson('/api/articles/9999'); 
 
         $response->assertStatus(404)
                  ->assertJson(['message' => 'Article not found']);
     }

     /**Test for scheduled article saving */
      /** @test */
    public function test_api_dispatches_fetch_articles_jobs()
    {
        // Fake the queue to prevent actual job execution
        Queue::fake();

        // Send GET request to the scheduler endpoint
        $response = $this->getJson('/api/scheduler');

        // Assert response status and message
        $response->assertStatus(200)
                 ->assertJson(['message' => 'Job has been dispatched!']);

        // Assert that the jobs were dispatched
        Queue::assertPushed(FetchArticlesFromNewsAPI::class);
        Queue::assertPushed(FetchArticlesFromNYT::class);
        Queue::assertPushed(FetchArticlesFromGuardian::class);
    }

    /**Test for fetching distinct category,source,authors*/
     /** @test */
     public function test_api_returns_distinct_categories_sources_and_authors()
     {
         Article::factory()->create([
             'category' => 'Business',
             'source' => 'BBC News',
             'author' => 'John Doe',
         ]);
 
         Article::factory()->create([
             'category' => 'Technology',
             'source' => 'TechCrunch',
             'author' => 'Jane Smith',
         ]);
 
         Article::factory()->create([
             'category' => 'Health',
             'source' => 'CNN',
             'author' => 'Dr. Adams',
         ]);
 
         $response = $this->getJson('/api/getpreferencesfields');
 
         $response->assertStatus(200);
 
         $response->assertJson([
             'categories' => ['Business', 'Technology', 'Health'],
             'sources' => ['BBC News', 'TechCrunch', 'CNN'],
             'authors' => ['John Doe', 'Jane Smith', 'Dr. Adams'],
         ]);
     }
 

}
