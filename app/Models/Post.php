<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tag1\Scolta\Export\ContentItem;
use Tag1\ScoltaLaravel\Searchable;

class Post extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'user_id',
        'body',
        'parent_id',
        'star_count',
        'reply_count',
        'boost_count',
    ];

    protected function casts(): array
    {
        return [
            'star_count' => 'integer',
            'reply_count' => 'integer',
            'boost_count' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Post::class, 'parent_id')->with('user')->orderBy('created_at');
    }

    public function hashtags(): BelongsToMany
    {
        return $this->belongsToMany(Hashtag::class);
    }

    public function isReply(): bool
    {
        return $this->parent_id !== null;
    }

    public function toSearchableContent(): ContentItem
    {
        $user = $this->relationLoaded('user') ? $this->user : $this->user()->first();
        $displayName = $user?->display_name ?? '';
        $username = $user?->username ?? '';

        // Include author context in the indexed body so "posts by fitness people"
        // can surface author names, and hashtags surface as search terms.
        $hashtagText = $this->relationLoaded('hashtags')
            ? $this->hashtags->pluck('name')->map(fn ($t) => '#'.$t)->implode(' ')
            : '';

        $bodyText = $this->body;
        if ($hashtagText) {
            $bodyText .= ' '.$hashtagText;
        }

        $bodyHtml = '<p>'.e($bodyText).'</p>';
        if ($displayName) {
            $bodyHtml .= '<p class="author">'.e($displayName).' @'.e($username).'</p>';
        }

        return new ContentItem(
            id: 'post-'.$this->id,
            title: $displayName ? $displayName.' on MyStream' : 'MyStream post',
            bodyHtml: $bodyHtml,
            url: route('posts.show', $this),
            date: $this->created_at->format('Y-m-d'),
            siteName: 'MyStream',
        );
    }

    public function scopeSearchable($query)
    {
        return $query->whereNull('parent_id');
    }
}
