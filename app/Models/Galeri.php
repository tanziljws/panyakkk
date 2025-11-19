<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Galeri extends Model
{
    use HasFactory;

    // IZINKAN FIELD judul, deskripsi, gambar, thumbnail dan category_id untuk mass assignment
    protected $fillable = ['judul', 'deskripsi', 'gambar', 'thumbnail', 'category_id'];

    /**
     * Get the likes for the gallery item.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Get the comments for the gallery item.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }

    /**
     * Check if a user has liked the gallery item.
     */
    public function isLikedByUser($userId)
    {
        return $this->likes()->where('user_id', $userId)->exists();
    }

    /**
     * Get the user who created the gallery item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category that owns the gallery item.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the image URL (always HTTPS)
     */
    public function getImageUrlAttribute()
    {
        if ($this->gambar) {
            // Use secure_asset to ensure HTTPS
            return secure_asset('images/' . $this->gambar);
        }
        return null;
    }

    /**
     * Get the thumbnail URL (always HTTPS)
     */
    public function getThumbnailUrlAttribute()
    {
        $image = $this->thumbnail ?? $this->gambar;
        if ($image) {
            // Use secure_asset to ensure HTTPS
            return secure_asset('images/' . $image);
        }
        return null;
    }
}
