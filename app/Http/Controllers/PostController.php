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
            'title' => 'nullable|string|max:255', // Тақырыпты валидациялау
            'content' => 'nullable|string',
            'location_id' => 'required|exists:locations,id',
            'images.*' => 'image|max:2048',
            'images' => 'array|max:10',
            'saved' => 'nullable|boolean', // Сақтауды валидациялау
        ]);

        $post = Post::create([
            'user_id' => auth()->id(),
            'location_id' => $request->location_id,
            'title' => $request->title, // Тақырыпты қосу
            'content' => $request->content,
            'saved' => $request->saved ?? false, // Сақтауды қосу, егер жоқ болса false
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('post_images', 'public');
                $post->images()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Пост сәтті құрылды', 'post' => $post->load('images')], 201);
    }


    // 📌 Нақты постты көру + комментарийлер орны
    public function show($id)
    {
        $post = Post::with(['user', 'location', 'images', 'comments.user'])->findOrFail($id);

        return response()->json($post);
    }

    // 📌 Постты жаңарту
    public function update(Request $request, $id)
    {
        $post = Post::findOrFail($id);

        // Авторлықты тексеру (қажет болса)
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Құқығыңыз жеткіліксіз'], 403);
        }

        $request->validate([
            'title' => 'nullable|string|max:255', // Тақырыпты валидациялау
            'content' => 'nullable|string',
            'location_id' => 'exists:locations,id',
            'images.*' => 'image|max:2048',
            'images' => 'array|max:10',
            'saved' => 'nullable|boolean', // Сақтауды валидациялау
        ]);

        $post->update([
            'title' => $request->title ?? $post->title, // Тақырыпты жаңарту
            'content' => $request->content,
            'location_id' => $request->location_id ?? $post->location_id,
            'saved' => $request->saved ?? $post->saved, // Сақтауды жаңарту
        ]);

        if ($request->hasFile('images')) {
            // Ескі суреттерді жою
            foreach ($post->images as $image) {
                Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }

            foreach ($request->file('images') as $image) {
                $path = $image->store('post_images', 'public');
                $post->images()->create(['image_path' => $path]);
            }
        }

        return response()->json(['message' => 'Пост жаңартылды', 'post' => $post->load('images')]);
    }

    // 📌 Постты жою
    public function destroy($id)
    {
        $post = Post::findOrFail($id);

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Құқығыңыз жеткіліксіз'], 403);
        }

        foreach ($post->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $post->delete();

        return response()->json(['message' => 'Пост жойылды']);
    }

}
