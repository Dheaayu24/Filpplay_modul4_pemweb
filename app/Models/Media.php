<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Media extends Model
{
    /** @use HasFactory<\Database\Factories\MediaFactory> */
    use HasFactory;

    protected $table = 'media';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'type',
        'genre',
        'year',
        'episodes',
        'country',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'episodes' => 'integer',
        ];
    }

    /**
     * Users who have added this media to their collection.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Format media details consistently with the FlipPlay Modul 3 OOP output.
     */
    protected function details(): Attribute
    {
        return Attribute::make(
            get: function (): string {
                if ($this->type === 'drama') {
                    $country = $this->country ?? '-';
                    return "[Drama] {$this->title} | Genre: {$this->genre} | Tahun: {$this->year} | Episode: {$this->episodes} | Negara: {$country}";
                }

                return "[Anime] {$this->title} | Genre: {$this->genre} | Tahun: {$this->year} | Episode: {$this->episodes}";
            },
        );
    }

    /**
     * Filter media by partial title match.
     */
    public function scopeSearch(Builder $query, ?string $title): Builder
    {
        if ($title === null || trim($title) === '') {
            return $query;
        }

        return $query->where('title', 'like', '%' . trim($title) . '%');
    }

    /**
     * Filter media by type (anime/drama).
     */
    public function scopeFilterType(Builder $query, ?string $type): Builder
    {
        if ($type === null || trim($type) === '') {
            return $query;
        }

        return $query->where('type', strtolower(trim($type)));
    }
}
