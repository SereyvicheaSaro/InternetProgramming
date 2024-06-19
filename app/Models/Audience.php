<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Audience extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'article_id',
        'user_id'
    ];
    // An audience has one user
    public function user():BelongsTo{
        return $this->belongsTo(User::class);
    }
    
    // An audience have many comments
    public function comments(): MorphMany{
        return $this->morphMany(Comment::class, 'commentable');
    }
}
