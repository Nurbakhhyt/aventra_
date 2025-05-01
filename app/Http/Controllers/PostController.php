<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::with(['user', 'location', 'images'])->latest()->get();

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'nullable|string',
            'location_id' => 'required|exists:locations,id',
            'images.*' => 'image|max:2048',
            'images' => 'array|max:10',
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'location_id' => $request->location_id,
            'content' => $request->content,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('post_images', 'public');
                $post->images()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Пост успешно создан', 'post' => $post->load('images')], 201);
    }


    // 📌 Посмотреть конкретный пост + место для комментариев
    public function show($id)
    {
        $post = Post::with(['user', 'location', 'images', 'comments.user'])->findOrFail($id);

        return response()->json($post);
    }

    // 📌 Обновить пост
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Проверка авторства (если нужно)
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        $request->validate([
            'content' => 'nullable|string',
            'location_id' => 'exists:locations,id',
            'images.*' => 'image|max:2048',
            'images' => 'array|max:10',
        ]);

        $post->update([
            'content' => $request->content,
            'location_id' => $request->location_id ?? $post->location_id,
        ]);

        if ($request->hasFile('images')) {
            // Удалить старые изображения
            foreach ($post->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            foreach ($request->file('images') as $image) {
                $path = $image->store('post_images', 'public');
                $post->images()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Пост обновлён', 'post' => $post->load('images')]);
    }

    // 📌 Удалить пост
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Недостаточно прав'], 403);
        }

        foreach ($post->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $post->delete();

        return response()->json(['message' => 'Пост удалён']);
    }

}
