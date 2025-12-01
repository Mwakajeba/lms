<?php

if (!function_exists('current_company_id')) {
    /**
     * Get the current company ID from config
     *
     * @return string|null
     */
    function current_company_id() {
        return config('app.current_company_id');
    }
}

if (!function_exists('current_branch_id')) {
    /**
     * Get the current branch ID from config
     *
     * @return string|null
     */
    function current_branch_id() {
        return config('app.current_branch_id');
    }
}

if (!function_exists('current_company')) {
    /**
     * Get the current company model
     *
     * @return \App\Models\Company|null
     */
    function current_company() {
        $companyId = current_company_id();
        return $companyId ? \App\Models\Company::find($companyId) : null;
    }
}

if (!function_exists('current_branch')) {
    /**
     * Get the current branch model
     *
     * @return \App\Models\Branch|null
     */
    function current_branch() {
        $branchId = current_branch_id();
        return $branchId ? \App\Models\Branch::find($branchId) : null;
    }
} 