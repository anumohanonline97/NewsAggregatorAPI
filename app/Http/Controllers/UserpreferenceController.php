<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use App\Models\UserPreference;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserpreferenceController extends Controller
{
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

