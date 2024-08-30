<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $posts = Post::where('user_id', Auth::id())
            ->orderBy('pinned', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($posts);
    }


    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'nullable|image|mimes:jpg,png,jpeg|max:2048',
            'pinned' => 'required|boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id'
        ]);

        $coverImagePath = null;
        if ($request->hasFile('cover_image')) {
            $coverImagePath = $request->file('cover_image')->store('cover_images', 'public');
        }

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'body' => $request->body,
            'cover_image' => $coverImagePath,
            'pinned' => $request->pinned,
        ]);

        if ($request->has('tags')) {
            $post->tags()->attach($request->tags);
        }

        return response()->json($post, 201);
    }



    public function show(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        return response()->json($post);
    }


    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'cover_image' => 'sometimes|image|mimes:jpg,png,jpeg',
            'pinned' => 'required|boolean',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id'
        ]);

        if ($request->hasFile('cover_image')) {
            if ($post->cover_image) {
                Storage::delete($post->cover_image);
            }
            $post->cover_image = $request->file('cover_image')->store('cover_images');
        }

        $post->update([
            'title' => $request->title,
            'body' => $request->body,
            'cover_image' => $post->cover_image,
            'pinned' => $request->pinned,
        ]);

        $post->tags()->sync($request->tags);

        return response()->json($post);
    }

    public function destroy(Post $post)
    {
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 400);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully']);
    }


    public function trashed()
    {
        $posts = Post::onlyTrashed()->where('user_id', Auth::id())->get();

        return response()->json($posts);
    }

    public function restore($id)
    {
        $post = Post::onlyTrashed()->where('user_id', Auth::id())->findOrFail($id);
        $post->restore();

        return response()->json(['message' => 'Post restored successfully']);
    }
}
