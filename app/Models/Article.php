<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Article extends Model
{
    use HasApiTokens,HasFactory, Notifiable;

    protected $fillable = [
        'title',
        'author',
        'source',
        'category',
        'description',
        'url',
        'published_at',
    ];

}
