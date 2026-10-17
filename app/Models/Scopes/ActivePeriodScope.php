<?php

namespace App\Models\Scopes;

use App\Models\Period;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Session;

class ActivePeriodScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $periodId = Session::get('selected_period_id', function () {
            return cache()->remember('active_period_id', 3600, function () {
                try {
                    return Period::where('is_active', true)->value('id');
                } catch (\Throwable $e) {
                    return null;
                }
            });
        });

        if ($periodId) {
            $builder->where($model->getTable().'.period_id', $periodId);
        } else {
            $builder->whereRaw('1 = 0');
        }
    }
}
