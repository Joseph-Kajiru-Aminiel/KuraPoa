<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitution;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'title', 'slug', 'description', 'starts_at', 'ends_at', 'status', 'institution_id', 'is_test',
    'is_public', 'appeals_deadline_at',
])]
class Election extends Model
{
    use HasFactory;
    use ScopesToInstitution;

    protected static function booted(): void
    {
        static::creating(function (Election $election): void {
            if (blank($election->slug)) {
                $base = Str::slug($election->title) ?: 'uchaguzi';
                $slug = $base;
                $suffix = 2;

                while (static::query()->where('slug', $slug)->exists()) {
                    $slug = $base.'-'.$suffix++;
                }

                $election->slug = $slug;
            }
        });

        static::created(function (Election $election): void {
            if (! $election->stages()->exists()) {
                ElectionStage::createDefaultsFor($election);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'appeals_deadline_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    protected function institutionScopeRelation(): string
    {
        return 'institution';
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class)->orderBy('sort_order');
    }

    public function stages(): HasMany
    {
        return $this->hasMany(ElectionStage::class)->orderBy('sort_order');
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ElectionDocument::class);
    }

    public function observers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'observer_elections');
    }

    public function voters(): HasMany
    {
        return $this->hasMany(Voter::class);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed'
            || $this->ends_at->isPast();
    }

    public function isVotingOpen(): bool
    {
        return $this->status === 'open'
            && now()->greaterThanOrEqualTo($this->starts_at)
            && now()->lessThanOrEqualTo($this->ends_at);
    }

    public function isPubliclyVisible(): bool
    {
        return $this->is_public && in_array($this->status, ['scheduled', 'open', 'closed'], true);
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_public', true)->whereIn('status', ['scheduled', 'open', 'closed']);
    }
}
