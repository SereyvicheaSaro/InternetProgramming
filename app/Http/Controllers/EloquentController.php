<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Audience;
use App\Models\Author;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EloquentController extends Controller
{
    public function createAuthor(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => $request->get('username'),
        ]);

        Author::create([
            'name' => $request->get('name'),
            'user_id' => $user->id,
        ]);

        return response()->json(['message' => 'Author created '. $user->name], 201);
    }

    public function createArticle(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'author' => 'required|string',
        ]);

        $author = Author::where('name', $request->get('author'))->first();

        if ($author) {
            Article::create([
                'name' => $request->get('name'),
                'author_id' => $author->id,
            ]);
            return response()->json(['Article created successfully for author ' . $author->name], 201);
        }

        return response()->json(['message' => 'Author not found'], 404);
    }

    public function createAudience(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = User::create([
            'name' => Str::lower($request->get('name')),
        ]);

        if ($user) {
            Audience::create([
                'name' => $request->get('name'),
                'user_id' => $user->id,
            ]);

            return response("Audience created successfully");
        }

        return response("Failed to create audience", 500);
    }

    public function subscribe(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'article' => 'required|array',
            'article.*' => 'required|string|max:255',
        ]);

        $audience = Audience::where('name', $request->get('name'))->first();

        if (!$audience) {
            return response("Audience not found", 404);
        }

        $articleNames = $request->get('article');
        $articles = Article::whereIn('name', $articleNames)->get();

        if ($articles->isEmpty()) {
            return response("None of the articles found", 404);
        }

        foreach ($articles as $article) {
            // Check if the audience is already subscribed to this article
            $existingSubscription = Audience::where('name', $audience->name)
                                            ->where('article_id', $article->id)
                                            ->first();

            if (!$existingSubscription) {
                // Subscribe audience to the article
                Audience::create([
                    'name' => $audience->name,
                    'user_id' => $audience->user_id,
                    'article_id' => $article->id,
                ]);
            }
        }

        return response("Audience subscribed to articles successfully", 200);
    }


    public function comment(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'comment' => 'required|string|max:255',
            'comment_type' => 'required|string|max:255',
            'comment_to' => 'nullable|string|max:255'
        ]);

        $user = Author::where('name', $request->get('name'))->first() ?? 
                Audience::where('name', $request->get('name'))->first();

        if (!$user) {
            return response("User with name " . $request->get('name') . " does not exist", 404);
        }

        $comment = new Comment([
            'name' => $request->get('comment'),
            'user_id' => $user->user_id,
        ]);

        switch ($request->get('comment_type')) {
            case 'article':
                $article = Article::where('name', $request->get('comment_to'))->first();
                if ($article) {
                    $article->comments()->save($comment);
                } else {
                    return response("Article with name " . $request->get('comment_to') . " does not exist", 404);
                }
                break;

            case 'audience':
                $audience = Audience::where('name', $request->get('comment_to'))->first();
                if ($audience) {
                    $audience->comments()->save($comment);
                } else {
                    return response("Audience with name " . $request->get('comment_to') . " does not exist", 404);
                }
                break;

            case 'author':
                $author = Author::where('name', $request->get('comment_to'))->first();
                if ($author) {
                    $author->comments()->save($comment);
                } else {
                    return response("Author with name " . $request->get('comment_to') . " does not exist", 404);
                }
                break;
        }

        return response("Commented Successfully");
    }

    public function getArticles($name)
    {
        $author = Author::with('articles')->where('name', $name)->first();

        if ($author) {
            return response($author->articles);
        }

        return response("Author not found", 404);
    }

    public function getAudience($article)
    {
        $article = Article::with('audiences')->where('name', $article)->first();

        if ($article) {
            return response($article->audiences);
        }

        return response("Article not found", 404);
    }

    public function getAudienceByAuthor($authorName)
    {
        $author = Author::with('audiences')->where('name', $authorName)->first();

        if ($author) {
            $uniqueAudiences = $author->audiences->unique('name')->values();
            return response($uniqueAudiences);
        }

        return response("Author not found", 404);
    }

    public function getComment($topic)
    {
        switch ($topic) {
            case 'author':
                $authors = Author::with('comments')->get();
                return response($authors);
            case 'audience':
                $audiences = Audience::with('comments')->get();
                return response($audiences);
            case 'article':
                $articles = Article::with('comments')->get();
                return response($articles);
            default:
                return response("Invalid topic", 400);
        }
    }
}
