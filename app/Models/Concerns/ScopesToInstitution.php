<?php

namespace App\Models\Concerns;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait ScopesToInstitution
{
    protected static function bootScopesToInstitution(): void
    {
        static::addGlobalScope('institution', function (Builder $query): void {
            /** @var User|null $user */
            $user = auth()->user();

            if (! $user?->is_admin && ! $user?->is_super_admin) {
                return;
            }

            $institutionId = $user->is_super_admin
                ? request()->integer('institution_id') ?: null
                : $user->institution_id;

            if ($institutionId === null && ! $user->is_super_admin) {
                $query->whereRaw('1 = 0');

                return;
            }

            if ($institutionId !== null) {
                $relation = (new static)->institutionScopeRelation();
                if ($relation === '') {
                    $query->where($query->getModel()->qualifyColumn('id'), $institutionId);
                } else {
                    $query->whereHas($relation, fn (Builder $related) => $related->where('institutions.id', $institutionId));
                }
            }
        });
    }

    protected function institutionScopeRelation(): string
    {
        return '';
    }
}
