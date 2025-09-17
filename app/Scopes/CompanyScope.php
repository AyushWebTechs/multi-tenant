<?php
namespace App\Scopes;

use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CompanyScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        if (app()->bound('currentCompany') && ($company = app('currentCompany'))) {
            $builder->where($model->getTable() . '.id', $company->id);
        }
    }
}
