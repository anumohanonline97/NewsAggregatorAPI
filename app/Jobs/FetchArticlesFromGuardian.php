<?php

namespace App\Jobs;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FetchArticlesFromGuardian implements ShouldQueue
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
        $this->FetchArticlesFromGuardian();
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

    private function fetchArticlesFromGuardian()
    {
        $guardApiKey = config('services.datasource.guardApi.key');
        $guardApiUrl = config('services.datasource.guardApi.url');

        $fromDate = date('Y-m-d', strtotime('-1 day'));
        $toDate = date('Y-m-d', strtotime('-1 day'));

        $apiUrl = "{$guardApiUrl}?from-date={$fromDate}&to-date={$toDate}=api-key={$guardApiKey}";

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
