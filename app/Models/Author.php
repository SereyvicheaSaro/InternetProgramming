<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Author extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'user_id'
    ];
    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }
    
    public function articles():HasMany{
        return $this->hasMany(Article::class);
    }

    public function comments(): MorphMany{
        return $this->morphMany(Comment::class, "commentable");
    }

    public function audiences(): HasManyThrough{
        return $this->hasManyThrough(Audience::class, Article::class);
    }
}
