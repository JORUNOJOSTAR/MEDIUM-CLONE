<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Post extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use HasSlug;
    protected $fillable = [
        'title',
        'content',
        // 'image',
        'slug',
        'user_id',
        'category_id',
        'published_at',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('preview')->width(400);

        $this->addMediaConversion('large')->width(1200);
    }

    public function registerMediaCollections():void{
        $this->addMediaCollection('default')
            ->singleFile();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function claps()
    {
        return $this->hasMany(Clap::class);
    }

    public function readTime($wordsPerMinute = 100)
    {
        $wordCount = str_word_count(strip_tags($this->content));
        $minutes = ceil($wordCount / $wordsPerMinute);
        return max(1, $minutes);
    }

    public function imageUrl($conversionName = '')
    {
        $media = $this->getFirstMedia();
        if ($media?->hasGeneratedConversion($conversionName)) {
            return $media->getUrl($conversionName);
        }

        return $media?->getUrl();
    }

    public function getCreatedDate()
    {
        return $this->created_at->format('M d, Y');
    }
}
