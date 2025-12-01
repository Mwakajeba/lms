<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Menu;

use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot()
    {
        View::composer('incs.navBar', function ($view) {
            $user = \Auth::user();
            $branchIds = [];
            // Check if user has selected a branch (change branch)
            $selectedBranchId = session('branch_id');
            if ($selectedBranchId) {
                $branchIds = [$selectedBranchId];
            } elseif ($user) {
                // If user has many branches
                if (method_exists($user, 'branches')) {
                    $branchIds = $user->branches()->pluck('branches.id')->toArray();
                }
                // If user has single branch
                elseif (isset($user->branch_id)) {
                    $branchIds = [$user->branch_id];
                }
            }

            // Use the comprehensive arrears data method
            $arrearsData = $this->getArrearsDataForNavbar($branchIds);
            
            // Filter for 1-30 days and format for navbar
            $arrearsLoans = collect($arrearsData)
                ->filter(function($item) {
                    return $item['days_in_arrears'] >= 1 && $item['days_in_arrears'] <= 30;
                })
                ->map(function($item) {
                    return (object)[
                        'loan_id' => $item['loan_id'],
                        'customer_name' => $item['customer'],
                        'customer_no' => $item['customer_no'],
                        'loanNo' => $item['loan_no'],
                        'amount_in_arrears' => $item['arrears_amount'],
                        'days_in_arrears' => $item['days_in_arrears'],
                    ];
                });

            $view->with('arrearsLoans', $arrearsLoans);
            $view->with('arrearsLoansCount', $arrearsLoans->count());
        });

        View::composer('incs.sideMenu', function ($view) {
            $user = Auth::user();

            if (!$user) {
                $view->with('menus', []);
                return;
            }

            // Get all user roles
            $userRoles = $user->roles;

            if ($userRoles->isEmpty()) {
                $view->with('menus', []);
                return;
            }

            // Get role IDs
            $roleIds = $userRoles->pluck('id')->toArray();

            // Get menus for all user roles
            $menus = Menu::with('children')
                ->whereNull('parent_id')
                ->whereHas('roles', function ($query) use ($roleIds) {
                    $query->whereIn('roles.id', $roleIds);
                })
                ->get();

            $view->with('menus', $menus);
        });
    }

    private function getArrearsDataForNavbar($branchIds = [])
    {
        $today = \Carbon\Carbon::now();
        
        $loansQuery = \App\Models\Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule.repayments'])
                          ->where('status', 'active');

        if (!empty($branchIds)) {
            $loansQuery->whereIn('branch_id', $branchIds);
        }

        $loans = $loansQuery->get();
        $arrearsData = [];

        foreach ($loans as $loan) {
            $totalArrears = 0;
            $daysInArrears = 0;
            $firstOverdueDate = null;
            $overdueSchedules = [];

            // Check each schedule item for overdue amounts
            foreach ($loan->schedule->sortBy('due_date') as $schedule) {
                $dueDate = \Carbon\Carbon::parse($schedule->due_date);
                
                if ($dueDate->lt($today) && $schedule->remaining_amount > 0) {
                    $totalArrears += $schedule->remaining_amount;
                    $overdueSchedules[] = $schedule;
                    
                    if (!$firstOverdueDate) {
                        $firstOverdueDate = $dueDate;
                        $daysInArrears = round($firstOverdueDate->diffInDays($today));
                    }
                }
            }

            // Only include loans that have arrears
            if ($totalArrears > 0) {
                $arrearsData[] = [
                    'loan_id' => $loan->id,
                    'customer' => $loan->customer->name ?? 'N/A',
                    'customer_no' => $loan->customer->customerNo ?? 'N/A',
                    'phone' => $loan->customer->phone1 ?? 'N/A',
                    'loan_no' => $loan->loanNo ?? 'N/A',
                    'loan_amount' => $loan->amount,
                    'disbursed_date' => $loan->disbursed_on ? \Carbon\Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                    'branch' => $loan->branch->name ?? 'N/A',
                    'group' => $loan->group->name ?? 'N/A',
                    'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                    'arrears_amount' => $totalArrears,
                    'days_in_arrears' => $daysInArrears,
                    'first_overdue_date' => $firstOverdueDate ? $firstOverdueDate->format('d-m-Y') : 'N/A',
                    'overdue_schedules_count' => count($overdueSchedules),
                ];
            }
        }

        // Sort by days in arrears (highest first)
        usort($arrearsData, function($a, $b) {
            return $b['days_in_arrears'] - $a['days_in_arrears'];
        });

        return $arrearsData;
    }
}
