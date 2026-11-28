<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitution;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'position_id', 'name', 'photo', 'photo_thumbnail', 'manifesto', 'reg_number', 'department_id', 'status',
    'rejection_reason', 'verified_at', 'verified_by',
])]
class Candidate extends Model
{
    use ScopesToInstitution;

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    protected function institutionScopeRelation(): string
    {
        return 'position.election.institution';
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
