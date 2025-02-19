<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserpreferenceController extends Controller
{

    /**
 * @OA\Post(
 *     path="/api/saveuserpreferences",
 *     summary="Save user news preferences",
 *     description="Stores the user's preferred news category, source, and author.",
 *     tags={"User Preferences"},
 *     security={{ "sanctum": {} }},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="category", type="string", nullable=true, example="business"),
 *             @OA\Property(property="source", type="string", nullable=true, example="CNN"),
 *             @OA\Property(property="author", type="string", nullable=true, example="John Doe")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="User preferences saved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="User preferences saved successfully!"),
 *             @OA\Property(property="preference", type="object",
 *                 @OA\Property(property="user_id", type="integer", example=2),
 *                 @OA\Property(property="category", type="string", example="business"),
 *                 @OA\Property(property="source", type="string", example="CNN"),
 *                 @OA\Property(property="author", type="string", example="John Doe"),
 *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-18T19:16:24.000000Z"),
 *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-18T19:16:24.000000Z"),
 *                 @OA\Property(property="id", type="integer", example=1)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="errors", type="object",
 *                 @OA\Property(property="category", type="array", @OA\Items(type="string"), example={"The category field must be a string."}),
 *                 @OA\Property(property="source", type="array", @OA\Items(type="string"), example={"The source field must be a string."}),
 *                 @OA\Property(property="author", type="array", @OA\Items(type="string"), example={"The author field must be a string."})
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=429,
 *         description="Too many requests",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Too many requests, please slow down.")
 *         )
 *     )
 * )
 */
    public function saveUserPreference(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category' => 'nullable|string',
            'source' => 'nullable|string',
            'author' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();

        $userpreference = new UserPreference();

        $userpreference->user_id =  $userId;
        $userpreference->category =  $request->category;
        $userpreference->source =  $request->source;
        $userpreference->author =  $request->author;

        $userpreference->save();
      
        return response()->json([
            'message' => 'User preferences saved successfully!',
            'preference' => $userpreference
        ],201);
    }

    /**
 * @OA\Get(
 *     path="/api/getuserpreferencesnews",
 *     summary="Get user preference-based news",
 *     description="Fetches news articles based on the user's saved preferences (category, source, author). If no preferences are found, returns all news articles.",
 *     tags={"User Preferences"},
 *     security={{ "sanctum": {} }},
 *     @OA\Response(
 *         response=200,
 *         description="Preferred news articles retrieved successfully",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Preferred news articles retrieved successfully!"),
 *             @OA\Property(property="articles", type="object",
 *                 @OA\Property(property="current_page", type="integer", example=1),
 *                 @OA\Property(property="data", type="array", @OA\Items(
 *                     type="object",
 *                     @OA\Property(property="id", type="integer", example=1),
 *                     @OA\Property(property="title", type="string", example="State Department had a plan to buy $400M worth of armored Tesla vehicles"),
 *                     @OA\Property(property="author", type="string", example="mlive.com"),
 *                     @OA\Property(property="source", type="string", example="Biztoc.com"),
 *                     @OA\Property(property="category", type="string", example="business"),
 *                     @OA\Property(property="description", type="string", example="State Department was in talks with Tesla for armored EVs."),
 *                     @OA\Property(property="url", type="string", format="uri", example="https://example.com/article"),
 *                     @OA\Property(property="published_at", type="string", format="date-time", example="2025-02-17 18:37:00"),
 *                     @OA\Property(property="created_at", type="string", format="date-time", example="2025-02-18T17:13:08.000000Z"),
 *                     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-02-18T17:13:08.000000Z")
 *                 )),
 *                 @OA\Property(property="first_page_url", type="string", example="http://localhost:8082/api/getuserpreferencesnews?page=1"),
 *                 @OA\Property(property="last_page", type="integer", example=2),
 *                 @OA\Property(property="next_page_url", type="string", nullable=true, example="http://localhost:8082/api/getuserpreferencesnews?page=2"),
 *                 @OA\Property(property="prev_page_url", type="string", nullable=true, example=null),
 *                 @OA\Property(property="per_page", type="integer", example=10),
 *                 @OA\Property(property="total", type="integer", example=20)
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     ),
 *     @OA\Response(
 *         response=429,
 *         description="Too many requests",
 *         @OA\JsonContent(
 *             type="object",
 *             @OA\Property(property="message", type="string", example="Too many requests, please slow down.")
 *         )
 *     )
 * )
 */
    public function getUserPreferenceNews(){
        $user = Auth::user();
        $preferences = UserPreference::where('user_id', $user->id)->first();
    
        if (!$preferences) {
            return response()->json([
                'message' => 'No preferences found, returning all articles.',
                'articles' => Article::paginate(10)
            ],200);
        }
    
        $query = Article::query();
    
        if ($preferences->category) {
            $query->orWhere('category', $preferences->category);
        }
        if ($preferences->source) {
            $query->orWhere('source', $preferences->source);
        }
        if ($preferences->author) {
            $query->orWhere('author', $preferences->author);
        }
    
        $articles = $query->paginate(10);
    
        return response()->json([
            'message' => 'Preferred news articles retrieved successfully!',
            'articles' => $articles
        ], 200);
    }
}

