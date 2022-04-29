<?php

namespace App\Http\Controllers\Design;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Design;
use App\Models\User;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, $designId)
    {
        $request->validate([
            'body' => ['required', 'max:255']
        ]);
        $design = Design::find($designId);
        $comment = $design->comments()->create([
            'user_id' => auth()->id(),
            'body' => $request->body
        ]);

        return new CommentResource($comment);
    }

    public function edit(Request $request, $id)
    {
        $request->validate([
            'body' => ['required', 'max:255']
        ]);
        $comment = Comment::find($id);
        $this->authorize('update', $comment);
        $comment->update([
            'body' => $request->body
        ]);

        return new CommentResource($comment);
    }

    public function destroy($id)
    {
        $comment = Comment::find($id);
        $this->authorize('delete', $comment);
        $comment->delete();

        return response()->json([
            'message' => 'your comment deleted'
        ], 200);
    }
}
