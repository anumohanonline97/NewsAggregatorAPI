<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = [
        'title',
        'author',
        'source',
        'description',
        'url',
        'published_at',
    ];

}
