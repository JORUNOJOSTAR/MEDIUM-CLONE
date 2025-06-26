<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostCreateRequest;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \DB::listen(function ($query){
            \Log::info($query->sql);
        });
        $user = auth()->user();
        $query = Post::with(['user','media'])
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
        
        // $image = $data['image'];
        
        $data['user_id'] = Auth::id();
        $data['slug'] = Str::slug($data['title']);
        
        // $imagePath = $image->store('posts','public');
        // $data['image'] = $imagePath;


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
        return view('post.show',[
            'post' => $post
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
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
