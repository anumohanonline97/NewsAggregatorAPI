<?php

namespace App\Jobs;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchArticles implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        // You can pass parameters here if needed
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info("jobs");
        // Fetch articles from multiple sources
        // $this->fetchArticlesFromNewsAPI();
        // $this->fetchArticlesFromNYT();
        // $this->fetchArticlesFromGuardian();

    }

    /**
     * Helper function to make API calls.
     *
     * @param string $url
     * @return array
     */
    private function fetchApiData($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent: MyLaravelApp/1.0']);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            Log::error("cURL error: " . curl_error($ch));
        }
        curl_close($ch);

        return $response ? json_decode($response, true) : [];
    }

    /**
     * Fetch articles from NewsAPI.org.
     */
    private function fetchArticlesFromNewsAPI()
    {
        $newsApiKey = config('services.datasource.newsApi.key');
        $newsApiUrl = config('services.datasource.newsApi.url');
        $fromDate = date('Y-m-d', strtotime('-1 day'));
        $toDate = date('Y-m-d');

        $apiUrl = "{$newsApiUrl}?q=apple&from={$fromDate}&to={$toDate}&apiKey={$newsApiKey}";

        $newsData = $this->fetchApiData($apiUrl);

        if (isset($newsData['articles'])) {
            foreach ($newsData['articles'] as $article) {
                Article::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'title' => $article['title'],
                        'author' => $article['author'] ?? 'Unknown',
                        'description' => $article['description'],
                        'url' => $article['url'],
                        'source' => $article['source']['name'],
                        'published_at' => Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s'),
                    ]
                );
            }
        }
    }

    /**
     * Fetch articles from New York Times API.
     */
    private function fetchArticlesFromNYT()
    {
        $nytApiKey = config('services.datasource.nytApi.key');
        $nytApiUrl = config('services.datasource.nytApi.url');

        $apiUrl = "{$nytApiUrl}?api-key={$nytApiKey}";

        $newsData = $this->fetchApiData($apiUrl);

        if (isset($newsData['results'])) {
            foreach ($newsData['results'] as $article) {
                Article::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'title' => $article['title'],
                        'author' => $article['byline'] ?? 'Unknown',
                        'description' => $article['abstract'],
                        'url' => $article['url'],
                        'source' => 'New York Times',
                        'published_at' => isset($article['published_date']) ? Carbon::parse($article['published_date'])->format('Y-m-d H:i:s') : null,
                    ]
                );
            }
        }
    }

    /**
     * Fetch articles from The Guardian API.
     */
    private function fetchArticlesFromGuardian()
    {
        $guardApiKey = config('services.datasource.guardApi.key');
        $guardApiUrl = config('services.datasource.guardApi.url');

        $apiUrl = "{$guardApiUrl}?api-key={$guardApiKey}";

        $newsData = $this->fetchApiData($apiUrl);

        if (isset($newsData['response']['results'])) {
            foreach ($newsData['response']['results'] as $article) {
                Article::updateOrCreate(
                    ['url' => $article['webUrl']],
                    [
                        'title' => $article['webTitle'],
                        'author' => $article['fields']['byline'] ?? 'Unknown',
                        'description' => $article['fields']['trailText'] ?? 'No description',
                        'url' => $article['webUrl'],
                        'source' => 'The Guardian',
                        'published_at' => isset($article['webPublicationDate']) ? Carbon::parse($article['webPublicationDate'])->format('Y-m-d H:i:s') : null,
                    ]
                );
            }
        }
    }
}
