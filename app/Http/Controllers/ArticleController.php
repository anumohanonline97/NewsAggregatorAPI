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
        $news_api_key = config('services.datasource.news_api');
        dd($news_api_key);
    }
}
