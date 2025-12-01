<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CompanyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Global scope for company scoping
        Builder::macro('forCompany', function ($companyId = null) {
            $companyId = $companyId ?? config('app.current_company_id');
            
            if ($companyId && $this->getModel()->getTable() !== 'companies') {
                return $this->where('company_id', $companyId);
            }
            
            return $this;
        });

        Builder::macro('forBranch', function ($branchId = null) {
            $branchId = $branchId ?? config('app.current_branch_id');
            
            if ($branchId && $this->getModel()->getTable() !== 'branches') {
                return $this->where('branch_id', $branchId);
            }
            
            return $this;
        });

        // Helper functions
        if (!function_exists('current_company_id')) {
            function current_company_id() {
                return config('app.current_company_id');
            }
        }

        if (!function_exists('current_branch_id')) {
            function current_branch_id() {
                return config('app.current_branch_id');
            }
        }
    }
}
