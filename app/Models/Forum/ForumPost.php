<?php

namespace App\Models\Forum;

use App\Models\Building;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ForumPost extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $guarded = [];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function parent()
    {
        return $this->belongsTo(ForumPost::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(ForumPost::class, 'parent_id');
    }

    public function forumLikes()
    {
        return $this->hasMany(ForumLike::class);
    }

    public function isLikedBy(User $user)
    {
        return $this->forumLikes()->where('user_id', $user->id)->exists();
    }
}
