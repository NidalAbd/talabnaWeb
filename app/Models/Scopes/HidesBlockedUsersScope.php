<?php

namespace App\Models\Scopes;

use App\Support\Blocks;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Hides rows authored by users the signed-in API user has blocked, on every query of the model
 * (feeds, categories, search, favourites, comments...). Does nothing for guests, the admin web
 * guard, queues or commands, so back-office tooling still sees everything.
 */
class HidesBlockedUsersScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $hidden = Blocks::hiddenFromCurrentUser();
        if ($hidden) {
            $builder->whereNotIn($model->qualifyColumn('user_id'), $hidden);
        }
    }
}
