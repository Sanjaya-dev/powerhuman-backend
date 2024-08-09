<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 6);

        $companyQuery = Company::with('users')->whereHas('users', function($query) {
            $query->where('user_id', Auth::id());
        });
        //powerhuman.com/api/company?id=1
        if ($id) {
            $company = $companyQuery->find($id);
            if($company) {
                return ResponseFormatter::success($company, 'Company found');
            }

            return ResponseFormatter::error('Company not found', 404);
        }

        // powerhuman.com/api/company?name=company
        $companies = $companyQuery;

        if($name) {
            $companies->where('name', 'like', '%' . $name . '%');
            
        }
            return ResponseFormatter::success($companies->paginate($limit), 'Company found');
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            // uplaod logo
            if($request->HasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }
            // Create company
            $company = Company::create([
                'name' => $request->name,
                'logo' => $path,
            ]);

            if(!$company) {
                throw new Exception('Company not created');
            }
            // attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);
            //load user at company
            $company->load('users');
            // return response
            return ResponseFormatter::success($company, 'Company created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            // get company by id
            $company = Company::find($id);
            
            // check if company exists
            if(!$company) {
                throw new Exception('Company not found');
            }

            // uplaod logo
            if($request->HasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // update company
            $company->update([
                'name' => $request->name ? $request->name:$company->name,
                'logo' => $request->logo ? $path:$company->logo,
            ]);

            // return response
            return ResponseFormatter::success($company, 'Company updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
