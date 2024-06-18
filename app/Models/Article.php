<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Article extends Model
{
    use HasFactory;
    protected $fillable =[
        'name',
        'author_id'
    ];
    public function audiences() :HasMany{
        return $this->hasMany(Audience::class);
    }
    public function comments() :MorphMany{
        return $this->morphMany(Comment::class, 'commentable');
    }
}
