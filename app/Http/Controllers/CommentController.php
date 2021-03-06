<?php

namespace App\Http\Controllers;

use App\Comment;
use App\Events\NewCommentEvent;
use App\Post;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Post $post)
    {
        return response()->json($post->comments()->with('user')->latest()->get());
    }

    public function store(Request $request, Post $post)
    {
        $comment = $post->comments()->create([
            'body' => $request->body,
            'user_id' => auth()->id(),
        ]);
        $comment = Comment::where('id', $comment->id)->with('user')->first();
        //dd($comment);
        broadcast(new NewCommentEvent($comment))->toOthers();
        //event(new NewCommentEvent($comment));
        return $comment->toJson();
    }
}
