<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $totalCompanies = Company::count();
        $totalBranches = Branch::count();
        $totalUsers = User::count();
        $activeCompanies = Company::where('status', 'active')->count();
        
        return view('super-admin.dashboard', compact('totalCompanies', 'totalBranches', 'totalUsers', 'activeCompanies'));
    }

    public function companies()
    {
        $companies = Company::with('branches')->paginate(10);
        return view('super-admin.companies.index', compact('companies'));
    }

    public function createCompany()
    {
        return view('super-admin.companies.create');
    }

    public function storeCompany(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:companies,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'license_number' => 'required|string|unique:companies,license_number',
            'registration_date' => 'required|date',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->all();
        $data['company_id'] = Str::uuid();
        $data['status'] = 'active';

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/companies'), $logoName);
            $data['logo'] = 'uploads/companies/' . $logoName;
        }

        Company::create($data);

        return redirect()->route('super-admin.companies')->with('success', 'Company registered successfully!');
    }

    public function showCompany(Company $company)
    {
        $company->load(['branches', 'users']);
        return view('super-admin.companies.show', compact('company'));
    }

    public function editCompany(Company $company)
    {
        return view('super-admin.companies.edit', compact('company'));
    }

    public function updateCompany(Request $request, Company $company)
    {
        // Custom validation for email to handle existing email
        $emailRules = 'required|email';
        if ($request->email !== $company->email) {
            $emailRules .= '|unique:companies,email,' . $company->id . ',id';
        }
        
        // Custom validation for license_number to handle existing license
        $licenseRules = 'required|string';
        if ($request->license_number !== $company->license_number) {
            $licenseRules .= '|unique:companies,license_number,' . $company->id . ',id';
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => $emailRules,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'license_number' => $licenseRules,
            'registration_date' => 'required|date',
            'status' => 'required|in:active,inactive,suspended',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = $request->except('logo');

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/companies'), $logoName);
            $data['logo'] = 'uploads/companies/' . $logoName;
        }

        $company->update($data);

        return redirect()->route('super-admin.companies')->with('success', 'Company updated successfully!');
    }

    public function destroyCompany(Company $company)
    {
        $company->delete();
        return redirect()->route('super-admin.companies')->with('success', 'Company deleted successfully!');
    }

    public function branches()
    {
        $branches = Branch::with(['company', 'users'])->paginate(10);
        return view('super-admin.branches.index', compact('branches'));
    }

    public function users()
    {
        $users = User::with(['company', 'branch'])->paginate(10);
        return view('super-admin.users.index', compact('users'));
    }
}
