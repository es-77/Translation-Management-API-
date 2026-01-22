<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Builder;

/**
 * Translation model for storing localized text content.
 *
 * @property int $id
 * @property string $key
 * @property string $locale
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Tag> $tags
 */
class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'locale',
        'value',
    ];

    /**
     * Get the tags associated with the translation.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Scope a query to filter by locale.
     */
    public function scopeByLocale(Builder $query, string $locale): Builder
    {
        return $query->where('locale', $locale);
    }

    /**
     * Scope a query to filter by key pattern.
     */
    public function scopeByKeyPattern(Builder $query, string $pattern): Builder
    {
        return $query->where('key', 'like', "%{$pattern}%");
    }

    /**
     * Scope a query to filter by content.
     */
    public function scopeByContent(Builder $query, string $content): Builder
    {
        return $query->where('value', 'like', "%{$content}%");
    }

    /**
     * Scope a query to filter by tags.
     *
     * @param array<int>|int $tagIds
     */
    public function scopeByTags(Builder $query, array|int $tagIds): Builder
    {
        $tagIds = is_array($tagIds) ? $tagIds : [$tagIds];

        return $query->whereHas('tags', function (Builder $query) use ($tagIds) {
            $query->whereIn('tags.id', $tagIds);
        });
    }
}
