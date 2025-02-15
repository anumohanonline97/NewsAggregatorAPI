<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Article;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ArticleController extends Controller
{
    public function saveArticle(Request $request){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'author' => 'required|string',
            'source' => 'required|string',
            'description' => 'required|string',
            'url' => 'required|string',
            'published_at' => 'required|date', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $article = new Article();

        $article->title = $request->title;
        $article->author = $request->author;
        $article->source = $request->source;
        $article->description = $request->description;
        $article->url = $request->url;
        $article->published_at = $request->published_at;
    
        $article->save();

        return response()->json(['message' => 'Article saved successfully!'],201);
    }

    public function updateArticle(Request $request,$id){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'author' => 'required|string',
            'source' => 'required|string',
            'description' => 'required|string',
            'url' => 'required|string',
            'published_at' => 'required|date', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $article = Article::find($id);

        if (!$article) {
            return response()->json(['message' => 'Article not found'], 404);
        }

        $article->title = $request->title;
        $article->author = $request->author;
        $article->source = $request->source;
        $article->description = $request->description;
        $article->url = $request->url;
        $article->published_at = $request->published_at;
    
        $article->save();

        return response()->json([
            'message'  => 'Article updated successfully!',
            'articles' => $article
            ],200);
    }

    public function listArticles(){
        $articles = Article::all();

        return response()->json([
            'message' => 'Articles listed successfully!',
            'articles' => $articles
        ], 200);
    }

    public function getArticle($id){

        $article = Article::find($id);

        if(!$article){
            return response()->json(['message' => 'Article not found'], 404);
        }

        return response()->json([
            'message' => 'Article retrieved successfully!',
            'articles' => $article
        ], 200);
    }

    public function deleteArticle($id){
        $article = Article::find($id);

        if(!$article){
            return response()->json(['message' => 'Article not found'], 404);
        }

        $article->delete();

        return response()->json([
            'message' => 'Article deleted successfully!',
        ], 200);
    }

    public function fetchNewsApiDataSource()
    {
        $newsApiKey = config('services.datasource.newsApi.key');
        $newsApiUrl = config('services.datasource.newsApi.url');
    
        $fromDate = date('Y-m-d', strtotime('-1 day')); 
        $toDate = date('Y-m-d'); 
        
        $apiUrl = $newsApiUrl."?q=apple&from={$fromDate}&to={$toDate}&apiKey={$newsApiKey}";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: MyLaravelApp/1.0'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $newsData = json_decode($response, true);

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

        return response()->json([
            'message' => 'Article saved successfully',
            'data' => $newsData
        ], 201);
    }

    public function fetchNewsApiDataSource1()
    {
        $nytApiKey = config('services.datasource.nytApi.key');
        $nytApiUrl = config('services.datasource.nytApi.url');    
        
        $apiUrl = $nytApiUrl."?api-key={$nytApiKey}";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: MyLaravelApp/1.0'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $newsData = json_decode($response, true);

        return response()->json($newsData, 200);
    }

    public function fetchNewsApiDataSource2()
    {
        $guardApiKey = config('services.datasource.guardApi.key');
        $guardApiUrl = config('services.datasource.guardApi.url');    
        
        $apiUrl = $guardApiUrl."?api-key={$guardApiKey}";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: MyLaravelApp/1.0'
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $newsData = json_decode($response, true);

        return response()->json($newsData, 200);
    }
}
