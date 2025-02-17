<?php

namespace App\Http\Controllers;

use App\Jobs\FetchArticles;
use App\Jobs\FetchArticlesFromGuardian;
use App\Jobs\FetchArticlesFromNewsAPI;
use App\Jobs\FetchArticlesFromNYT;
use Illuminate\Support\Facades\Validator;
use App\Models\Article;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ArticleController extends Controller
{
    /**
 * @OA\Post(
 *     path="/api/articles",
 *     summary="Save an Article",
 *     description="Saves a new article to the database.",
 *     tags={"Articles"},
 *     security={{"bearerAuth":{}}}, 
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "author", "source", "description", "url", "published_at"},
 *             @OA\Property(property="title", type="string", example="Latest Tech Trends"),
 *             @OA\Property(property="author", type="string", example="John Doe"),
 *             @OA\Property(property="source", type="string", example="Tech News Daily"),
 *             @OA\Property(property="description", type="string", example="An in-depth analysis of the latest trends in technology."),
 *             @OA\Property(property="url", type="string", format="url", example="https://technewsdaily.com/latest-trends"),
 *             @OA\Property(property="published_at", type="string", format="date-time", example="2024-02-15T10:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Article saved successfully!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Article saved successfully!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object", example={
 *                 "title": {"The title field is required."}, 
 *                 "published_at": {"The published_at field must be a valid date."}
 *             })
 *         )
 *     )
 * )
 */
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

    /**
 * @OA\Put(
 *     path="/api/articles/{id}",
 *     summary="Update an Article",
 *     description="Updates an existing article in the database.",
 *     tags={"Articles"},
 *     security={{"bearerAuth":{}}}, 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the article to update",
 *         @OA\Schema(type="integer", example=5)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"title", "author", "source", "description", "url", "published_at"},
 *             @OA\Property(property="title", type="string", example="Updated Tech Trends"),
 *             @OA\Property(property="author", type="string", example="Jane Doe"),
 *             @OA\Property(property="source", type="string", example="Updated Source"),
 *             @OA\Property(property="description", type="string", example="An updated description of tech trends."),
 *             @OA\Property(property="url", type="string", format="url", example="https://updatedsource.com/updated-article"),
 *             @OA\Property(property="published_at", type="string", format="date-time", example="2025-02-02T00:00:00Z")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Article updated successfully!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Article updated successfully!"),
 *             @OA\Property(property="articles", type="object",
 *                 @OA\Property(property="id", type="integer", example=5),
 *                 @OA\Property(property="title", type="string", example="update test"),
 *                 @OA\Property(property="author", type="string", example="update author"),
 *                 @OA\Property(property="source", type="string", example="update test"),
 *                 @OA\Property(property="description", type="string", example="test update"),
 *                 @OA\Property(property="content", type="string", example="test content update"),
 *                 @OA\Property(property="published_at", type="string", format="date", example="2025-02-02"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T07:14:02.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T08:02:58.000000Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Article not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Article not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="errors", type="object", example={
 *                 "title": {"The title field is required."}, 
 *                 "published_at": {"The published_at field must be a valid date."}
 *             })
 *         )
 *     )
 * )
 */

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

    /**
 * @OA\Get(
 *     path="/api/articles",
 *     summary="Get list of articles",
 *     description="Retrieves all articles from the database.",
 *     tags={"Articles"},
 *     security={{"bearerAuth":{}}}, 
 *     @OA\Response(
 *         response=200,
 *         description="Articles listed successfully!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Articles listed successfully!"),
 *             @OA\Property(property="articles", type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="State Department had a plan to buy $400M worth of armored Tesla vehicles"),
 *                     @OA\Property(property="author", type="string", example="mlive.com"),
 *                     @OA\Property(property="source", type="string", example="Biztoc.com"),
 *                     @OA\Property(property="description", type="string", example="By ADRIANA GOMEZ LICON Associated Press"),
 *                     @OA\Property(property="content", type="string", example="Tesla's (TSLA) beat-up stock has found support on the charts, for now."),
 *                     @OA\Property(property="published_at", type="string", format="date-time", nullable=true, example="2025-02-14T18:07:29.000000Z"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-14T18:07:29.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-14T18:07:29.000000Z")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

 public function listArticles()
 {
     $search = request()->query('search', null);
     $date = request()->query('date', null);
     $perPage = request()->query('per_page', 10);
 
     $query = Article::query();
 
     if (!empty($search)) {
         $query->where(function ($q) use ($search) {
             $q->where('title', 'like', "%{$search}%")
               ->orWhere('description', 'like', "%{$search}%");
         });
     }
 
     if (!empty($date)) {
         $query->whereDate('published_at', $date);
     }
 
     $articles = $query->paginate($perPage);
 
     return response()->json([
         'message' => 'Articles listed successfully!',
         'articles' => $articles
     ], 200);
 }
 

    /**
 * @OA\Get(
 *     path="/api/articles/{id}",
 *     summary="Get an article by ID",
 *     description="Retrieves a single article by its ID.",
 *     tags={"Articles"},
 *     security={{"bearerAuth":{}}}, 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the article to retrieve",
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Article retrieved successfully!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Article retrieved successfully!"),
 *             @OA\Property(property="articles", type="object",
 *                 @OA\Property(property="id", type="integer", example=3),
 *                 @OA\Property(property="title", type="string", example="test"),
 *                 @OA\Property(property="author", type="string", example="test author"),
 *                 @OA\Property(property="source", type="string", example="test source"),
 *                 @OA\Property(property="description", type="string", example="test description"),
 *                 @OA\Property(property="content", type="string", example="test content"),
 *                 @OA\Property(property="publishedAt", type="string", format="date-time", example="2025-01-01 00:00:00"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-15T06:55:36.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-15T06:55:36.000000Z")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Article not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Article not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

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

    /**
 * @OA\Delete(
 *     path="/api/articles/{id}",
 *     summary="Delete an article by ID",
 *     description="Deletes a specific article by its ID.",
 *     tags={"Articles"},
 *     security={{"bearerAuth":{}}}, 
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="ID of the article to delete",
 *         @OA\Schema(type="integer", example=3)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Article deleted successfully!",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Article deleted successfully!")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Article not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Article not found")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */

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

    public function fetchArticles()
    {
        FetchArticlesFromNewsAPI::dispatch();
        FetchArticlesFromNYT::dispatch();
        FetchArticlesFromGuardian::dispatch();


        return response()->json(['message' => 'Job has been dispatched!']);
    }
}
