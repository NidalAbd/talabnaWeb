<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{

    public function index($postId)
    {
        $comments = Comment::with(['user:id,name', 'user.photos'])->where('service_post_id', $postId)->paginate(10);
        return response()->json($comments);
    }


    public function create()
    {
        //
    }

    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'service_post_id' => 'required|exists:service_posts,id',
            'content' => 'required|string',
        ]);

        // Add the user_id from the currently authenticated user
        $validatedData['user_id'] = auth()->id();

        $comment = Comment::create($validatedData);

        return response()->json($comment, 201); // Return the created comment with HTTP status 201
    }


    public function show(Comment $comment)
    {
        return response()->json($comment);
    }


    public function update(Request $request, Comment $comment)
    {
        $validatedData = $request->validate([
            'content' => 'required|string',
        ]);

        // Update the comment content
        $comment->update([
            'content' => $validatedData['content'],
        ]);

        return response()->json($comment);
    }


    public function destroy(Comment $comment): JsonResponse
    {
        // Delete the comment
        $comment->delete();

        return response()->json(['message' => 'Comment deleted']);
    }
}
