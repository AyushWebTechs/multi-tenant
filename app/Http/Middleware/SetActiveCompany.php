<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;


class SetActiveCompany
{
public function handle($request, Closure $next)
{
if (Auth::check()) {
$user = Auth::user();
if ($user->active_company_id) {
$company = Company::where('id', $user->active_company_id)
->where('user_id', $user->id)
->first();


if ($company) {
// 1) bind into the container
app()->instance('currentCompany', $company);


// 2) attach to request attributes
$request->attributes->set('currentCompany', $company);
}
}
}


return $next($request);
}
}