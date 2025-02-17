<?php

namespace App\Jobs;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchArticlesFromNewsAPI implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->fetchArticlesFromNewsAPI();
    }

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
        $toDate = date('Y-m-d', strtotime('-1 day'));

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
}
