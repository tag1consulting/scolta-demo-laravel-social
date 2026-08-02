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
    /**
     * How many hashtag badges a search result card paints.
     *
     * Two, because the handle and the star count share the same row and a
     * third chip wrapped it at the search page's width.
     */
    public const CARD_BADGE_LIMIT = 2;

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
        return $this->belongsToMany(Hashtag::class, 'post_hashtag');
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

        if (! $this->relationLoaded('hashtags')) {
            $this->load('hashtags');
        }
        $hashtagNames = $this->hashtags->pluck('name');

        $hashtagText = $hashtagNames->map(fn ($t) => '#'.$t)->implode(' ');

        $bodyText = $this->body;
        if ($hashtagText) {
            $bodyText .= ' '.$hashtagText;
        }

        $bodyHtml = '<p>'.e($bodyText).'</p>';
        if ($displayName) {
            $bodyHtml .= '<p class="author">'.e($displayName).' @'.e($username).'</p>';
        }

        $filters = [];
        if ($hashtagNames->isNotEmpty()) {
            $filters['hashtag'] = $hashtagNames->all();
        }

        // Per-post data the search result cards paint: the author's avatar,
        // their handle, the star count and the post's hashtags. It rides along
        // in the fragment's meta map, so a card and a search-as-you-type
        // suggestion each cost zero per-result server calls.
        //
        // Metadata costs only the bytes of its own keys in each fragment,
        // unlike sortable, which writes a corpus-wide pf_meta entry. The keys
        // "title" and "date" are deliberately avoided: they lose to the
        // built-in values on a collision.
        $metadata = [];

        // The avatar the rest of the site already shows for this author. It is
        // a remote DiceBear URL and stays one: it is what every other page of
        // this demo renders, the feed loads a dozen of them at a time, and
        // there is no local copy to point at instead. A card that showed a
        // different picture from the post page it links to would be worse than
        // one that shares the site's dependency.
        $avatar = $user?->avatar_url;
        if (is_string($avatar) && $avatar !== '') {
            $metadata['image'] = $avatar;
        }

        // No image_alt on purpose. The avatar is decorative here: the handle
        // is written out beside it, so alt text would announce the same name
        // twice to a screen reader.
        if ($username !== '') {
            $metadata['handle'] = '@'.$username;
        }

        // Only when there are any. 8046 of the 12541 indexed posts have at
        // least one star; printing "0 stars" on the other 4495 would spend a
        // slot to say nothing.
        if ($this->star_count > 0) {
            $metadata['stars'] = (string) $this->star_count;
        }

        // A display-only copy of the hashtag facet values, capped at two.
        // Taken from the filters resolved just above rather than re-read from
        // the relation, so a badge's text is character-for-character the facet
        // value it corresponds to. Only 1202 of the 12541 posts carry any, so
        // most cards show none.
        //
        // JSON rather than a delimited string: a hashtag is user-entered text,
        // so there is no separator a future one provably cannot contain.
        $badges = array_slice($filters['hashtag'] ?? [], 0, self::CARD_BADGE_LIMIT);
        if ($badges !== []) {
            $encoded = json_encode(array_values($badges));
            if (is_string($encoded)) {
                $metadata['badges'] = $encoded;
            }
        }

        return new ContentItem(
            id: 'post-'.$this->id,
            title: $displayName ? $displayName.' on MyStream' : 'MyStream post',
            bodyHtml: $bodyHtml,
            url: route('posts.show', $this),
            date: $this->created_at->format('Y-m-d'),
            siteName: 'MyStream',
            metadata: $metadata,
            sortable: [
                'star_count' => $this->star_count,
            ],
            filters: $filters,
        );
    }

    public function scopeSearchable($query)
    {
        return $query->whereNull('parent_id')->with(['user', 'hashtags']);
    }
}
