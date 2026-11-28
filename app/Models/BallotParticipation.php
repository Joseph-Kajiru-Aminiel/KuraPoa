<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['voter_id', 'position_id'])]
class BallotParticipation extends Model
{
    public $timestamps = false;

    public function voter(): BelongsTo
    {
        return $this->belongsTo(Voter::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }
}
