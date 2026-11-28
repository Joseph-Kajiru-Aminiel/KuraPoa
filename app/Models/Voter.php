<?php

namespace App\Models;

use App\Models\Concerns\ScopesToInstitution;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'election_id', 'reg_number', 'name', 'phone', 'access_code', 'access_code_fingerprint', 'has_voted',
    'voted_at', 'department_id', 'course_id', 'class_group_id', 'is_active', 'verified_at', 'verified_by', 'verification_note',
])]
#[Hidden(['access_code', 'access_code_fingerprint'])]
class Voter extends Model
{
    use ScopesToInstitution;

    protected function casts(): array
    {
        return [
            'has_voted' => 'boolean',
            'voted_at' => 'datetime',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function participations(): HasMany
    {
        return $this->hasMany(BallotParticipation::class);
    }

    protected function institutionScopeRelation(): string
    {
        return 'election.institution';
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function classGroup(): BelongsTo
    {
        return $this->belongsTo(ClassGroup::class);
    }
}
