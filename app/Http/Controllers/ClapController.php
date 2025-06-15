<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
class ClapController extends Controller
{
    public function clap(Post $post)
    {
        $hasClapped = auth()->user()->hasClapped($post);
        if(!$hasClapped){
            $post->claps()->create(['user_id'=>auth()->user()->id]);
        }else{
            $post->claps()->where('user_id',auth()->user()->id)->delete();
        }
        
        return response()->json([
            'clapsCount' => $post->claps()->count(),
        ]);
    }

    
}
