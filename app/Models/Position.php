<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitution;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['election_id', 'title', 'sort_order'])]
class Position extends Model
{
    use ScopesToInstitution;

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    protected function institutionScopeRelation(): string
    {
        return 'election.institution';
    }
}
