<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'institution_id', 'election_id', 'type', 'ticket_code', 'name', 'reg_number', 'contact', 'message',
    'status', 'public_reply', 'handled_by', 'replied_at',
])]
class Complaint extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['replied_at' => 'datetime'];
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ComplaintNote::class);
    }
}
