<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\PostUpdateRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $query = Post::with(['user','media'])
                ->where('published_at','<=',now())
                ->withCount('claps')
                ->latest();
        // $query = Post::latest();
        if($user){
            $ids = $user->following()->pluck('users.id');
            $query->whereIn('user_id',$ids);
        }
        $posts = $query->simplePaginate(5);
        return view('post.index',compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::get();
        return view('post.create',[
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PostCreateRequest $request)
    {
        $data = $request->validated();
        
        $data['user_id'] = Auth::id();

        $post = Post::create($data);
        $post->addMediaFromRequest('image')
            ->toMediaCollection();

        return redirect()->route('dashboard');
        
    }

    /**
     * Display the specified resource.
     */
    public function show(String $username,Post $post)
    {
        $is_owner = $post->user_id ===Auth::id();
        if($post->published_at>now() && !$is_owner){
            abort(404);
        }
        return view('post.show',[
            'post' => $post
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        if($post->user_id!==Auth::id()){
            abort(403);
        }
        $categories = Category::get();
        return view('post.edit',[
            'post'=> $post,
            'categories' => $categories
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PostUpdateRequest $request, Post $post)
    {
        if($post->user_id!==Auth::id()){
            abort(403);
        }
        $data = $request->validated();
        $post->update($data);
        if($data['image'] ?? false){
            $post->addMediaFromRequest('image')
            ->toMediaCollection();
        }
        return redirect()->route('myPosts');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        if($post->user_id!==Auth::id()){
            abort(403);
        }
        $post->delete();
        return redirect()->route('myPosts');
    }

    public function category(Category $category){
        $posts = $category->posts()
                ->where('published_at','<=',now())
                ->with(['user','media'])
                ->withCount('claps')
                ->latest()
                ->simplePaginate(5);
        return view('post.index',['posts' => $posts]);
    }

    public function myPosts(){
        $user = auth()->user();
        $posts = $user->posts()
                ->with(['user','media'])
                ->withCount('claps')
                ->latest()
                ->simplePaginate(5);
        return view('post.index',['posts' => $posts]);
    }
}
