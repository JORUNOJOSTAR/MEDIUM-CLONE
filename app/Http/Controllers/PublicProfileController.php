<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class PublicProfileController extends Controller
{
    public function show(Request $request,User $user){
        $posts = $user->posts()
                ->when(auth()->id() !== $user->id, function ($query) {
                    $query->where('published_at', '<=', now());
                })
                ->latest()->paginate();
        
        return view('profile.show',[
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
