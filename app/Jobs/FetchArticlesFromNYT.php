<?php

namespace App\Jobs;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchArticlesFromNYT implements ShouldQueue
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
        $this->FetchArticlesFromNYT();
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

    private function fetchArticlesFromNYT()
    {
        $nytApiKey = config('services.datasource.nytApi.key');
        $nytApiUrl = config('services.datasource.nytApi.url');

        $apiUrl = "{$nytApiUrl}2025/2.json?api-key={$nytApiKey}";

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


}
