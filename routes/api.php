<?php
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ArticleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware([EnsureFrontendRequestsAreStateful::class,'auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/passwordreset', [AuthController::class, 'passwordreset']);

    Route::get('/articles', [ArticleController::class, 'listArticles']);
    Route::post('/articles', [ArticleController::class, 'saveArticle']);
    Route::put('/articles/{id}', [ArticleController::class, 'updateArticle']);
    Route::get('/articles/{id}', [ArticleController::class, 'getArticle']);
    Route::delete('/articles/{id}', [ArticleController::class, 'deleteArticle']);

    Route::get('/newsdatasource', [ArticleController::class, 'fetchNewsApiDataSource']);

    Route::post('/logout', [AuthController::class, 'logout']);
});

Route::post('/login', [AuthController::class, 'login']);
Route::post('/signup', [AuthController::class, 'signup']);
