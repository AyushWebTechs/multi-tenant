<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Auth::user()->companies();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $perPage = $request->get('per_page', 10);
        $companies = $query->paginate($perPage);

        $activeCompanyId = Auth::user()->active_company_id;

        $companies->getCollection()->transform(function ($company) use ($activeCompanyId) {
            $company->is_active = $company->id === $activeCompanyId;
            return $company;
        });

        return response()->json(
            [
                'success' => true,
                'statusCode' => 200,
                'message' => 'Companies retrieved successfully',
                'data' => $companies->items(),
                'meta' => [
                    'current_page' => $companies->currentPage(),
                    'last_page' => $companies->lastPage(),
                    'per_page' => $companies->perPage(),
                    'total' => $companies->total(),
                ],
            ],
            200,
        );
    }

    public function store(StoreCompanyRequest $request)
    {
        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'statusCode' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        $company = Auth::user()->companies()->create($validator->validated());

        if (!Auth::user()->active_company_id) {
            Auth::user()->update(['active_company_id' => $company->id]);
        }

        return response()->json(
            [
                'success' => true,
                'statusCode' => 201,
                'message' => 'Company created successfully',
                'data' => $company,
            ],
            201,
        );
    }

    public function show(Company $company)
    {
        $this->authorizeCompanyOwner($company);
        return response()->json(
            [
                'success' => true,
                'statusCode' => 200,
                'message' => 'Company retrieved successfully',
                'data' => $company,
            ],
            200,
        );
    }

    public function update(UpdateCompanyRequest $request, Company $company)
    {
        $this->authorizeCompanyOwner($company);

        $validator = Validator::make($request->all(), $request->rules());
        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'statusCode' => 422,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ],
                422,
            );
        }

        $company->update($validator->validated());

        return response()->json(
            [
                'success' => true,
                'statusCode' => 200,
                'message' => 'Company updated successfully',
                'data' => $company,
            ],
            200,
        );
    }

    public function destroy(Company $company)
    {
        $this->authorizeCompanyOwner($company);
        $company->delete();

        $user = Auth::user();
        if ($user->active_company_id == $company->id) {
            $user->update(['active_company_id' => null]);
        }

        return response()->json(
            [
                'success' => true,
                'statusCode' => 200,
                'message' => 'Company deleted successfully',
                'data' => null,
            ],
            200,
        );
    }

    public function activate(Request $request, Company $company)
    {
        $this->authorizeCompanyOwner($company);
        $user = Auth::user();
        $user->update(['active_company_id' => $company->id]);

        return response()->json(
            [
                'success' => true,
                'statusCode' => 200,
                'message' => 'Active company set successfully',
                'data' => $company,
            ],
            200,
        );
    }

    protected function authorizeCompanyOwner(Company $company)
    {
        if ($company->user_id !== Auth::id()) {
            abort(
                response()->json(
                    [
                        'success' => false,
                        'statusCode' => 403,
                        'message' => 'Unauthorized',
                    ],
                    403,
                ),
            );
        }
    }
}
