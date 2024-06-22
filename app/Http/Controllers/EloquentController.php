<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Audience;
use App\Models\Author;
use App\Models\Comment;
use App\Models\User;
use GuzzleHttp\Psr7\Response;
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
            return response()->json(['message' => 'Audience created successfully'], 201);
        }
        return response()->json(['message' => 'Failed to create audience.', 401]);
    }

    public function subscribe(Request $request){
        $request->validate([
            'name' => "required|string|max:255",
            'article' => 'required|string|max:255'
        ]);

        $a = Audience::where('name' , '=', $request->get('name'))->first();
        $article = Article::where('name' , '=', $request->get('article'))->first();

        if($a!=null || $article != null){
            if($a->article_id==null){
                $a->article_id = $article->id;
                $a->save();
                return response("Audience have Subscribed to article ". $article->id);
            }else{
                Audience::create(['name'=>$a->name,'user_id' => $a->user_id, 'article_id'=>$article->id]);
                return response()->json(['message' => 'Audience subscribe to article ' . $article->name], 200);
            }
        }else{
            return response()->json(['message' => 'Article not found.'], 404);
        }

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
            return response()->json(['message' => 'Uer not found.'], 404);
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
                    return response()->json(['message'=> 'Article not found.'], 404);
                    // return response("Article with name " . $request->get('comment_to') . " does not exist", 404);
                }
                break;

            case 'audience':
                $audience = Audience::where('name', $request->get('comment_to'))->first();
                if ($audience) {
                    $audience->comments()->save($comment);
                } else {
                    return response()->json(['message'=> 'Audience not found.'], 404);
                    // return response("Audience with name " . $request->get('comment_to') . " does not exist", 404);
                }
                break;

            case 'author':
                $author = Author::where('name', $request->get('comment_to'))->first();
                if ($author) {
                    $author->comments()->save($comment);
                } else {
                    return response()->json(['message' => 'Author not found.'], 404);
                    // return response("Author with name " . $request->get('comment_to') . " does not exist", 404);
                }
                break;
        }
        return response()->json(['message' => 'Comment successfully.'], 201);
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
