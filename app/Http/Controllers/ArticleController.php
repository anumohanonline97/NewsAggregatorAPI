<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\Article;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function saveArticle(Request $request){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'author' => 'required|string',
            'source' => 'required|string',
            'description' => 'required|string',
            'content' => 'required|string',
            'publishedAt' => 'required|date', 
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
        $article->content = $request->content;
        $article->publishedAt = $request->publishedAt;
    
        $article->save();

        return response()->json(['message' => 'Article saved successfully!'],201);
    }

    public function updateArticle(Request $request,$id){

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'author' => 'required|string',
            'source' => 'required|string',
            'description' => 'required|string',
            'content' => 'required|string',
            'publishedAt' => 'required|date', 
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
        $article->content = $request->content;
        $article->publishedAt = $request->publishedAt;
    
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
        $newsApiKey = config('services.datasource.news_api.key');
        $newsApiUrl = config('services.datasource.news_api.url');
    
    
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

        return response()->json($newsData, 200);
    }

    public function fetchNewsApiDataSource1()
    {
        $newsApiKey = config('services.datasource.news_api.key');
        $newsApiUrl = config('services.datasource.news_api.url');
    
    
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

        return response()->json($newsData, 200);
    }
}
