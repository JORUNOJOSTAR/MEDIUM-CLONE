<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PublicProfileController extends Controller
{
    public function show(Request $request,User $user){
        $posts = $user->posts()
                ->when($user->id!==Auth::id(),function($query){
                    $query->where('published_at','<=',now());
                })
                ->latest()->paginate();
        
        return view('profile.show',[
            'user' => $user,
            'posts' => $posts,
        ]);
    }
}
