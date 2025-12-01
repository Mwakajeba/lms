<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Company;
use App\Models\Group;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\DisbursementsExport;
use App\Exports\RepaymentExport;
use App\Models\Repayment;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PortfolioAtRiskExport;
use App\Exports\PortfolioExport;
use App\Exports\PerformanceExport;
use App\Exports\DelinquencyExport;
use App\Exports\InternalPortfolioAnalysisExport;
use App\Exports\LoanSizeTypeExport;
use App\Exports\GenericArrayExport;
use PDF;

class LoanReportController extends Controller
{
    public function loanDisbursementReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        // Pata data ya kuchuja kutoka kwenye request, ukiweka default values
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $branchId = $request->input('branch_id');
        $companyId = $request->input('company_id');
        $groupId = $request->input('group_id');
        // Get the authenticated user if they are a loan officer
        $loanOfficerId = $request->input('loan_officer_id');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        info('start date: ' . $startDate);
        info('end date: ' . $endDate);
        info('branch: ' . $branchId);

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // Unda query ya loans na uweke filters
        $loansQuery = Loan::with(['customer', 'product', 'branch', 'loanOfficer', 'group'])
            ->whereBetween('disbursed_on', [$startDate, $endDate])
            ->whereIn('branch_id', $assignedBranchIds);

        // Weka filter ya branch
        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }

        // Weka filter ya company
        if ($companyId) {
            $loansQuery->whereHas('product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }
        if ($groupId) {
            $loansQuery->where('group_id', $groupId);
        }
        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }

        $disbursements = $loansQuery->get();

        // Kokotoa muhtasari wa ripoti
        $summary = [
            'total_disbursed' => $disbursements->sum('amount'),
            'loan_count' => $disbursements->count(),
            'average_disbursed' => $disbursements->count() > 0 ? $disbursements->sum('amount') / $disbursements->count() : 0,
            'total_interest_expected' => $disbursements->sum('interest_amount'),
        ];

        // Pata list ya companies na groups
        $companies = Company::all();
        $groups = Group::all();
        // Only show loan officers assigned to the selected branch (if any)
        $loanOfficers = User::whereHas('roles', function($q) {
            $q->where('name', 'like', '%officer%');
        })
        ->when($branchId, function($query) use ($branchId) {
            $query->whereHas('branches', function($q) use ($branchId) {
            $q->where('branches.id', $branchId);
            });
        })
        ->get();

        // Rudi na view ya ripoti
        return view('loans.reports.disbursed', compact('disbursements', 'summary', 'branches', 'companies','groups','loanOfficers'));
    }



    public function exportLoanDisbursement(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        // 1. Pata filters kutoka kwenye request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        $companyId = $request->input('company_id');
        $exportType = $request->input('export_type');
        $exportAction = $request->input('export_action', 'download'); // 'download' ni default

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // 2. Unda query ya loans na uweke filters kama ilivyo kwenye method ya report
        $loansQuery = Loan::with(['customer', 'product', 'branch', 'loanOfficer','group'])
            ->whereBetween('disbursed_on', [$startDate, $endDate])
            ->whereIn('branch_id', $assignedBranchIds);

        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($companyId) {
            $loansQuery->whereHas('product', function ($query) use ($companyId) {
                $query->where('company_id', $companyId);
            });
        }
        if( $groupId) {
            $loansQuery->where('group_id', $groupId);
        }
        if( $loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }

        $disbursements = $loansQuery->get();
        $branch = $branchId ? Branch::findOrFail($branchId) : (object)['name' => 'All Branches'];


        // 3. Tekeleza mantiki ya export kulingana na aina ya faili
        if ($exportType === 'pdf') {
            $pdf = PDF::loadView('loans.reports.pdf', compact('disbursements', 'startDate', 'endDate', 'branch'))
                ->setPaper('a3', 'landscape');

            if ($exportAction === 'view') {
                return $pdf->stream('loan_disbursement_report.pdf');  // Hii itaonyesha PDF kwenye browser
            }

            return $pdf->download('loan_disbursement_report.pdf'); // Hii itapakua (default
        } elseif ($exportType === 'excel') {
            // Hapa tunatumia Maatwebsite/Excel
            return Excel::download(new DisbursementsExport($disbursements), 'loan_disbursement_report.xlsx');
        }

        // Rudi na ujumbe wa kosa ikiwa aina ya export haijatambuliwa
        return response()->json(['message' => 'Invalid export type.'], 400);
    }

    /**
     * Loan Size Type report (bucket loans by principal into ranges)
     */
    public function loanSizeTypeReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');

        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $buckets = [
            ['label' => '0 - 500,000', 'min' => 0, 'max' => 500000],
            ['label' => '500,000 - 1,000,000', 'min' => 500000, 'max' => 1000000],
            ['label' => '1,000,000 - 2,000,000', 'min' => 1000000, 'max' => 2000000],
            ['label' => '2,000,000 - 5,000,000', 'min' => 2000000, 'max' => 5000000],
            ['label' => '5,000,000 - 10,000,000', 'min' => 5000000, 'max' => 10000000],
            ['label' => 'ABOVE 10,000,000', 'min' => 10000000, 'max' => null],
        ];

        $today = Carbon::today();

        $results = [];
        $grand = [
            'count' => 0,
            'loan_amount' => 0,
            'interest' => 0,
            'total_loan' => 0,
            'total_outstanding' => 0,
            'arrears_count' => 0,
            'arrears_amount' => 0,
            'delayed_count' => 0,
            'delayed_amount' => 0,
            'outstanding_in_delayed' => 0,
        ];

        foreach ($buckets as $bucket) {
            $loans = Loan::query()
                ->when($startDate && $endDate, function($q) use ($startDate, $endDate){
                    $q->whereBetween('disbursed_on', [$startDate, $endDate]);
                })
                ->whereIn('branch_id', $assignedBranchIds)
                ->when($branchId && $branchId !== 'all', function($q) use ($branchId){
                    $q->where('branch_id', $branchId);
                })
                ->when(!is_null($bucket['max']), function($q) use ($bucket){
                    $q->whereBetween('amount', [$bucket['min'], $bucket['max']]);
                }, function($q) use ($bucket){
                    $q->where('amount', '>', $bucket['min']);
                })
                ->with(['repayments', 'schedule'])
                ->get();

            $count = $loans->count();
            $loanAmount = (float) $loans->sum('amount');
            $interest = (float) $loans->sum('interest_amount');
            $totalLoan = $loanAmount + $interest;

            // Outstanding principal = principal - principal repaid
            $totalOutstanding = (float) $loans->sum(function($loan){
                $principalPaid = $loan->repayments->sum('principal');
                return max(0, ($loan->amount ?? 0) - $principalPaid);
            });

            // Arrears = schedules past due with remaining > 0
            $arrearsCount = 0; $arrearsAmount = 0; $delayedCount = 0; $delayedAmount = 0; $outstandingInDelayed = 0;
            foreach ($loans as $loan) {
                foreach ($loan->schedule as $sch) {
                    $remaining = max(0, ($sch->principal + $sch->interest + $sch->fee_amount + $sch->penalty_amount) - ($sch->repayments->sum('principal') + $sch->repayments->sum('interest') + $sch->repayments->sum('fee_amount') + $sch->repayments->sum('penalt_amount')));
                    if ($remaining <= 0) continue;

                    if (Carbon::parse($sch->due_date)->lt($today)) {
                        // in arrears
                        $arrearsCount++;
                        $arrearsAmount += $remaining;
                    }
                    // delayed: after due_date but within grace window
                    if ($sch->end_grace_date && Carbon::parse($sch->due_date)->lt($today) && Carbon::parse($sch->end_grace_date)->gte($today)) {
                        $delayedCount++;
                        $delayedAmount += $remaining;
                        $outstandingInDelayed += max(0, $sch->principal - $sch->repayments->sum('principal'));
                    }
                }
            }

            $row = [
                'label' => $bucket['label'],
                'count' => $count,
                'loan_amount' => $loanAmount,
                'interest' => $interest,
                'total_loan' => $totalLoan,
                'total_outstanding' => $totalOutstanding,
                'arrears_count' => $arrearsCount,
                'arrears_amount' => $arrearsAmount,
                'delayed_count' => $delayedCount,
                'delayed_amount' => $delayedAmount,
                'outstanding_in_delayed' => $outstandingInDelayed,
            ];

            // grand totals
            foreach ($grand as $k => $v) {
                $grand[$k] += $row[$k] ?? 0;
            }

            $results[] = $row;
        }

        return view('loans.reports.loan_size_type', [
            'rows' => $results,
            'grand' => $grand,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'company' => $company,
        ]);
    }

    public function loanSizeTypeExport(Request $request)
    {
        $view = $this->loanSizeTypeReport($request);
        $data = $view->getData();
        return \Maatwebsite\Excel\Facades\Excel::download(new LoanSizeTypeExport($data['rows'], $data['grand'], $data['startDate'], $data['endDate']), 'loan_size_type_report.xlsx');
    }

    public function loanSizeTypeExportPdf(Request $request)
    {
        $view = $this->loanSizeTypeReport($request);
        $data = $view->getData();
        $data['company'] = auth()->user()->company;
        $pdf = \PDF::loadView('loans.reports.loan_size_type_pdf', $data)->setPaper('a3', 'landscape');
        return $pdf->download('loan_size_type_report.pdf');
    }

    /**
     * Monthly Loan Performance Report
     * Columns: Month, Loan Given, Interest, Total Loan+Interest, Total Amount Collected, Outstanding, Actual Interest Collected, Performance%
     */
    public function monthlyPerformanceReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');

        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')->toArray();

        // Build months range
        $start = $startDate ? Carbon::parse($startDate)->startOfMonth() : Carbon::now()->startOfYear();
        $end = $endDate ? Carbon::parse($endDate)->endOfMonth() : Carbon::now()->endOfMonth();

        $months = [];
        $cursor = $start->copy();
        while ($cursor <= $end) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonthNoOverflow();
        }

        // Preload loans and repayments within range
        $loans = Loan::query()
            ->select('id','amount','interest_amount','disbursed_on','branch_id')
            ->whereBetween('disbursed_on', [$start->toDateString(), $end->toDateString()])
            ->whereIn('branch_id', $assignedBranchIds)
            ->when($branchId && $branchId !== 'all', fn($q)=>$q->where('branch_id',$branchId))
            ->get();

        // Preload ALL repayments for cohort loans (lifetime), regardless of payment date
        $repayments = Repayment::query()
            ->select('loan_id','payment_date','principal','interest','fee_amount','penalt_amount')
            ->when($branchId && $branchId !== 'all', function($q) use ($assignedBranchIds, $branchId){
                // Join to loans to filter branch
                $q->whereIn('loan_id', Loan::where('branch_id',$branchId)->pluck('id'));
            }, function($q) use ($assignedBranchIds){
                $q->whereIn('loan_id', Loan::whereIn('branch_id',$assignedBranchIds)->pluck('id'));
            })
            ->get();

        $rows = [];
        $grand = [
            'loan_given' => 0,
            'interest' => 0,
            'total_loan' => 0,
            'collected' => 0,
            'outstanding' => 0,
            'actual_interest_collected' => 0,
        ];

        foreach ($months as $ym) {
            [$y,$m] = explode('-', $ym);
            $loanGiven = (float) $loans->filter(function($l) use($y,$m){
                return Carbon::parse($l->disbursed_on)->format('Y')==$y && Carbon::parse($l->disbursed_on)->format('m')==$m;
            })->sum('amount');
            $interest = (float) $loans->filter(function($l) use($y,$m){
                return Carbon::parse($l->disbursed_on)->format('Y')==$y && Carbon::parse($l->disbursed_on)->format('m')==$m;
            })->sum('interest_amount');
            $totalLoan = $loanGiven + $interest;
            // Cohort repayments: sum all repayments (up to end date) for loans disbursed in this month
            $cohortLoanIds = $loans->filter(function($l) use($y,$m){
                return Carbon::parse($l->disbursed_on)->format('Y')==$y && Carbon::parse($l->disbursed_on)->format('m')==$m;
            })->pluck('id')->all();

            $collected = (float) $repayments->whereIn('loan_id', $cohortLoanIds)
                ->sum(function($r){
                    return ($r->principal ?? 0) + ($r->interest ?? 0) + ($r->fee_amount ?? 0) + ($r->penalt_amount ?? 0);
                });
            $outstanding = max(0, $totalLoan - $collected);
            $actualInterestCollected = min($interest, max(0, $collected - $loanGiven));
            $performance = $totalLoan > 0 ? round(min(1, $collected / $totalLoan) * 100, 2) : 0;

            $rows[] = [
                'month' => Carbon::createFromDate((int)$y,(int)$m,1)->format('M Y'),
                'loan_given' => $loanGiven,
                'interest' => $interest,
                'total_loan' => $totalLoan,
                'collected' => $collected,
                'outstanding' => $outstanding,
                'actual_interest_collected' => $actualInterestCollected,
                'performance' => $performance,
            ];

            $grand['loan_given'] += $loanGiven;
            $grand['interest'] += $interest;
            $grand['total_loan'] += $totalLoan;
            $grand['collected'] += $collected;
            $grand['outstanding'] += $outstanding;
            $grand['actual_interest_collected'] += $actualInterestCollected;
        }

        return view('loans.reports.monthly_performance', [
            'rows' => $rows,
            'grand' => $grand,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'branchId' => $branchId,
            'company' => $company,
        ]);
    }

    public function monthlyPerformanceExport(Request $request)
    {
        $view = $this->monthlyPerformanceReport($request);
        $data = $view->getData();
        $headings = ['MONTH','LOAN GIVEN','INTEREST','TOTAL LOAN + INTEREST','TOTAL AMOUNT COLLECTED','OUTSTANDING','ACTUAL INTEREST COLLECTED','PERFORMANCE %'];
        $array = [];
        foreach ($data['rows'] as $r) {
            $array[] = [
                $r['month'],
                $r['loan_given'],
                $r['interest'],
                $r['total_loan'],
                $r['collected'],
                $r['outstanding'],
                $r['actual_interest_collected'],
                $r['performance']
            ];
        }
        return \Maatwebsite\Excel\Facades\Excel::download(new GenericArrayExport($array, $headings), 'monthly_loan_performance.xlsx');
    }

    public function monthlyPerformanceExportPdf(Request $request)
    {
        $view = $this->monthlyPerformanceReport($request);
        $data = $view->getData();
        $pdf = \PDF::loadView('loans.reports.monthly_performance_pdf', $data)->setPaper('a4', 'landscape');
        return $pdf->download('monthly_loan_performance.pdf');
    }


    //////////REPAYMENT FUNCTION REPORT////

    public function getRepaymentReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        // 1. Pata filters kutoka kwenye request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');
        $exportType = $request->input('export_type');
        $exportAction = $request->input('export_action', 'download');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // 2. Unda query ya malipo
        $repaymentsQuery = Repayment::with(['loan.customer', 'loan.branch', 'loan.product', 'loan.loanOfficer'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereHas('loan', function ($query) use ($assignedBranchIds) {
                $query->whereIn('branch_id', $assignedBranchIds);
            });

        if ($branchId && $branchId !== 'all') {
            $repaymentsQuery->whereHas('loan', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }
        if ($groupId) {
            $repaymentsQuery->whereHas('loan', function ($query) use ($groupId) {
                $query->where('group_id', $groupId);
            });
        }
        if ($loanOfficerId) {
            $repaymentsQuery->whereHas('loan', function ($query) use ($loanOfficerId) {
                $query->where('loan_officer_id', $loanOfficerId);
            });
        }

        $repayments = $repaymentsQuery->get();

        // Calculate summary values correctly
        $summary['total_principal'] = $repayments->sum('principal');
        $summary['total_interest'] = $repayments->sum('interest');
        $summary['total_fees'] = $repayments->sum('fee_amount');
        $summary['total_penalty'] = $repayments->sum('penalt_amount');
        $summary['total_paid'] = $repayments->sum(function ($repayment) {
            return ($repayment->principal ?? 0) + ($repayment->interest ?? 0) + ($repayment->fee_amount ?? 0) + ($repayment->penalt_amount ?? 0);
        });
        $summary['repayment_count'] = $repayments->count();
        $summary['average_paid'] = $repayments->count() > 0 ? $summary['total_paid'] / $repayments->count() : 0;

        // 4. Pata data ya groups na loan officers
        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();

        return view('loans.reports.repayments.repayment', compact('repayments', 'summary', 'startDate', 'endDate', 'branches','loanOfficers','groups'));
    }


    public function exportLoanRepayment(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        // 1. Pata filters kutoka kwenye request
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');
        $exportType = $request->input('export_type');
        $exportAction = $request->input('export_action', 'download');

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        // 2. Unda query ya malipo
        $repaymentsQuery = Repayment::with(['loan.customer','loan.group', 'loan.branch', 'loan.product', 'loan.loanOfficer'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->whereHas('loan', function ($query) use ($assignedBranchIds) {
                $query->whereIn('branch_id', $assignedBranchIds);
            });

        if ($branchId && $branchId !== 'all') {
            $repaymentsQuery->whereHas('loan', function ($query) use ($branchId) {
                $query->where('branch_id', $branchId);
            });
        }
        if ($groupId) {
            $repaymentsQuery->whereHas('loan', function ($query) use ($groupId) {
                $query->where('group_id', $groupId);
            });
        }
        if ($loanOfficerId) {
            $repaymentsQuery->whereHas('loan', function ($query) use ($loanOfficerId) {
                $query->where('loan_officer_id', $loanOfficerId);
            });
        }

        $repayments = $repaymentsQuery->get();
        $summary['total_paid'] = $repayments->sum(function ($repayment) {
            return $repayment->sum('principal') + $repayment->sum('interest') + $repayment->sum('fee_amount') + $repayment->sum('penalt_amount');
        });

        $branch = $branchId ? Branch::findOrFail($branchId) : (object)['name' => 'All Branches'];
        if ($exportType === 'pdf') {
            $branch = $branchId ? Branch::findOrFail($branchId) : (object)['name' => 'All Branches'];
            $pdf = PDF::loadView('loans.reports.repayments.pdf', compact('repayments', 'summary', 'startDate', 'endDate', 'branch'))
                ->setPaper('a3', 'landscape');

            if ($exportAction === 'view') {
                return $pdf->stream('loan_repayment_report.pdf');
            }

            return $pdf->download('loan_repayment_report.pdf');
        } elseif ($exportType === 'excel') {
            // Hapa tunatumia Maatwebsite/Excel
            return Excel::download(new RepaymentExport($repayments), 'loan_disbursement_report.xlsx');
        }
        // ... kwa excel, utahitaji kuongeza mantiki hapa
        return response()->json(['message' => 'Invalid export type.'], 400);
    }
    /**
     * Display the Loan Aging Report view and data.
     */
    public function loanAgingReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $branchId = $request->input('branch_id');
        $loanOfficerId = $request->input('loan_officer_id');
        $exportType = $request->input('export_type');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $agingData = [];
        $loansQuery = Loan::with(['customer', 'branch', 'loanOfficer'])
            ->where('status', 'active')
            ->whereIn('branch_id', $assignedBranchIds);

        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }

        $loans = $loansQuery->get();

        foreach ($loans as $loan) {
            // Calculate overdue buckets for each loan
            $current = $bucket_1_30 = $bucket_31_60 = $bucket_61_90 = $bucket_91_plus = $total_overdue = 0;
            // Get schedules if available
            $schedules = $loan->schedules ?? [];
            if (method_exists($loan, 'schedules')) {
                $schedules = $loan->schedules()->get();
            }

            // Calculate total principal paid
            $totalPrincipalPaid = 0;
            if (method_exists($loan, 'repayments')) {
                $totalPrincipalPaid = $loan->repayments()->sum('principal');
            }
            $outstandingBalance = ($loan->amount ?? 0) - $totalPrincipalPaid;

            if (count($schedules) > 0) {
                foreach ($schedules as $schedule) {
                    $due = $schedule->due_date;
                    $dueAmount = $schedule->due_amount ?? ($schedule->principal_due + $schedule->interest_due + $schedule->fee_due + $schedule->penalty_due);
                    $paid = $schedule->paid_amount ?? 0;
                    $outstanding = max(0, $dueAmount - $paid);
                    if ($outstanding <= 0) continue;
                    $days = \Carbon\Carbon::parse($due)->diffInDays($asOfDate, false);
                    if ($days < 0) {
                        $current += $outstanding;
                    } elseif ($days <= 30) {
                        $bucket_1_30 += $outstanding;
                    } elseif ($days <= 60) {
                        $bucket_31_60 += $outstanding;
                    } elseif ($days <= 90) {
                        $bucket_61_90 += $outstanding;
                    } else {
                        $bucket_91_plus += $outstanding;
                    }
                    if ($days > 0) {
                        $total_overdue += $outstanding;
                    }
                }
            } else {
                // No schedules: bucket by days since disbursement if unpaid
                if ($outstandingBalance > 0 && !empty($loan->disbursed_on)) {
                    $days = \Carbon\Carbon::parse($loan->disbursed_on)->diffInDays($asOfDate, false);
                    if ($days < 0) {
                        $current = $outstandingBalance;
                    } elseif ($days <= 30) {
                        $bucket_1_30 = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    } elseif ($days <= 60) {
                        $bucket_31_60 = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    } elseif ($days <= 90) {
                        $bucket_61_90 = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    } else {
                        $bucket_91_plus = $outstandingBalance;
                        $total_overdue = $outstandingBalance;
                    }
                }
            }
            $agingData[] = [
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? 'N/A',
                'phone' => $loan->customer->phone1 ?? 'N/A',
                'loan_no' => $loan->loanNo ?? 'N/A',
                'amount' => $loan->amount ?? 0,
                'outstanding_balance' => $outstandingBalance,
                'disbursed_no' => $loan->disbursed_on ?? 'N/A',
                'expiry' => $loan->last_repayment_date ?? 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'current' => $current,
                'bucket_1_30' => $bucket_1_30,
                'bucket_31_60' => $bucket_31_60,
                'bucket_61_90' => $bucket_61_90,
                'bucket_91_plus' => $bucket_91_plus,
                'total_overdue' => $total_overdue,
            ];
        }

        // Handle export requests
        if ($exportType && !empty($agingData)) {
            if ($exportType === 'excel') {
                return $this->exportLoanAgingToExcel($agingData, $asOfDate, $branchId, $loanOfficerId);
            } elseif ($exportType === 'pdf') {
                return $this->exportLoanAgingToPdf($agingData, $asOfDate, $branchId, $loanOfficerId);
            }
        }

        // Only show data if filter applied
        $showData = $request->has('as_of_date') || $request->has('branch_id') || $request->has('loan_officer_id');
        return view('loans.reports.loan_aging', [
            'branches' => $branches,
            'loanOfficers' => $loanOfficers,
            'agingData' => $showData ? $agingData : null,
        ]);
    }

        /**
     * Display the Loan Outstanding Balance Report view and data.
     */
    public function loanOutstandingReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $asOfDate = $request->input('as_of_date', date('Y-m-d'));
        $branchId = $request->input('branch_id');
        $loanOfficerId = $request->input('loan_officer_id');
        $exportType = $request->input('export_type');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $loansQuery = \App\Models\Loan::with(['customer', 'branch', 'loanOfficer', 'schedule.repayments'])
            ->whereIn('status', ['active', 'written_off', 'defaulted'])
            ->whereIn('branch_id', $assignedBranchIds);
            
        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }
        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }
        $loans = $loansQuery->get();

        $outstandingData = [];
        $totalPrincipalDisbursed = 0;
        $totalExpectedInterest = 0;
        $totalPaidInterest = 0;
        $totalPrincipalPaid = 0;
        $totalOutstandingInterest = 0;
        $totalAccruedInterest = 0;
        $totalNotDueInterest = 0;

        $currentDate = \Carbon\Carbon::parse($asOfDate);
        $currentMonth = $currentDate->format('Y-m');

        foreach ($loans as $loan) {
            // Calculate repayments breakdown
            $principalPaid = $interestPaid = $feesPaid = $penaltyPaid = 0;
            if (method_exists($loan, 'repayments')) {
                $principalPaid = $loan->repayments()->sum('principal');
                $interestPaid = $loan->repayments()->sum('interest');
                $feesPaid = $loan->repayments()->sum('fee_amount');
                $penaltyPaid = $loan->repayments()->sum('penalt_amount');
            }

            // Calculate detailed interest breakdown from loan schedules
            $outstandingInterest = 0;
            $accruedInterest = 0;
            $notDueInterest = 0;

            if ($loan->schedule && $loan->schedule->count() > 0) {
                foreach ($loan->schedule as $schedule) {
                    $scheduleDate = \Carbon\Carbon::parse($schedule->due_date);
                    $scheduleMonth = $scheduleDate->format('Y-m');
                    $scheduleInterest = $schedule->interest ?? 0;

                    // Calculate interest paid for this schedule
                    $scheduleInterestPaid = $schedule->repayments->sum('interest');

                    if ($scheduleMonth <= $currentMonth) {
                        // Interest is due up to this month - what's not paid is outstanding
                        $outstandingInterest += max(0, $scheduleInterest - $scheduleInterestPaid);
                    } else {
                        // Interest is not yet due
                        $notDueInterest += $scheduleInterest;
                    }
                }

                // Calculate accrued interest (interest earned but not yet due)
                // This is interest that has been earned based on time elapsed but not yet due
                $loanStartDate = \Carbon\Carbon::parse($loan->disbursed_on);
                $monthsElapsed = $loanStartDate->diffInMonths($currentDate);
                $totalLoanMonths = $loan->period ?? 1;

                if ($monthsElapsed > 0 && $monthsElapsed < $totalLoanMonths) {
                    // Calculate proportional interest earned but not yet due
                    $accruedInterest = ($notDueInterest * $monthsElapsed) / $totalLoanMonths;
                }
            } else {
                // Fallback to simple calculation if no schedule
                $outstandingInterest = max(0, ($loan->interest_amount ?? 0) - $interestPaid);
                $notDueInterest = 0;
                $accruedInterest = 0;
            }

            // Calculate outstanding balance correctly: (Principal + Interest) - (Principal Paid + Interest Paid)
            $totalLoanAmount = ($loan->amount ?? 0) + ($loan->interest_amount ?? 0);
            $totalPaid = $principalPaid + $interestPaid;
            $outstandingBalance = $totalLoanAmount - $totalPaid;

            // Calculate expected interest verification: Interest Paid + Outstanding Interest + Not Due Interest
            $calculatedExpectedInterest = $interestPaid + $outstandingInterest + $notDueInterest;

            $outstandingData[] = [
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? 'N/A',
                'phone' => $loan->customer->phone1 ?? 'N/A',
                'loan_no' => $loan->loanNo ?? 'N/A',
                'amount' => $loan->amount ?? 0,
                'interest' => $loan->interest_amount ?? 0,
                'outstanding_balance' => $outstandingBalance,
                'disbursed_no' => $loan->disbursed_on ?? 'N/A',
                'expiry' => $loan->last_repayment_date ?? 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'principal_paid' => $principalPaid,
                'interest_paid' => $interestPaid,
                'fees_paid' => $feesPaid,
                'penalty_paid' => $penaltyPaid,
                'outstanding_interest' => $outstandingInterest,
                'accrued_interest' => $accruedInterest,
                'not_due_interest' => $notDueInterest,
                'calculated_expected_interest' => $calculatedExpectedInterest,
            ];
            $totalPrincipalDisbursed += ($loan->amount ?? 0);
            $totalExpectedInterest += ($loan->interest_amount ?? 0);
            $totalPaidInterest += $interestPaid;
            $totalPrincipalPaid += $principalPaid;
            $totalOutstandingInterest += $outstandingInterest;
            $totalAccruedInterest += $accruedInterest;
            $totalNotDueInterest += $notDueInterest;
        }

        // Calculate total expected interest from components for verification
        $totalCalculatedExpectedInterest = $totalPaidInterest + $totalOutstandingInterest + $totalNotDueInterest;

        $summary = [
            'total_principal_disbursed' => $totalPrincipalDisbursed,
            'total_expected_interest' => $totalExpectedInterest,
            'total_paid_interest' => $totalPaidInterest,
            'total_principal_paid' => $totalPrincipalPaid,
            'total_outstanding_interest' => $totalOutstandingInterest,
            'total_accrued_interest' => $totalAccruedInterest,
            'total_not_due_interest' => $totalNotDueInterest,
            'total_calculated_expected_interest' => $totalCalculatedExpectedInterest,
        ];

        // Handle export requests
        if ($exportType && !empty($outstandingData)) {
            if ($exportType === 'excel') {
                return $this->exportLoanOutstandingToExcel($outstandingData, $summary, $asOfDate, $branchId, $loanOfficerId);
            } elseif ($exportType === 'pdf') {
                return $this->exportLoanOutstandingToPdf($outstandingData, $summary, $asOfDate, $branchId, $loanOfficerId);
            }
        }

        // Only show data if filter applied
        $showData = $request->has('as_of_date') || $request->has('branch_id') || $request->has('loan_officer_id');
        return view('loans.reports.loan_outstanding', [
            'branches' => $branches,
            'loanOfficers' => $loanOfficers,
            'outstandingData' => $showData ? $outstandingData : null,
            'summary' => $summary,
        ]);
    }

    /**
     * Export Loan Aging Report to Excel
     */
    private function exportLoanAgingToExcel($agingData, $asOfDate, $branchId = null, $loanOfficerId = null)
    {
        $branch = $branchId ? Branch::find($branchId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;

        return \Maatwebsite\Excel\Facades\Excel::download(new class($agingData, $asOfDate, $branch, $loanOfficer) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            private $agingData;
            private $asOfDate;
            private $branch;
            private $loanOfficer;

            public function __construct($agingData, $asOfDate, $branch, $loanOfficer)
            {
                $this->agingData = collect($agingData);
                $this->asOfDate = $asOfDate;
                $this->branch = $branch;
                $this->loanOfficer = $loanOfficer;
            }

            public function collection()
            {
                return $this->agingData->map(function ($row) {
                    return [
                        $row['customer'],
                        $row['customer_no'],
                        $row['phone'],
                        $row['loan_no'],
                        $row['amount'],
                        $row['outstanding_balance'],
                        $row['disbursed_no'],
                        $row['expiry'],
                        $row['branch'],
                        $row['loan_officer'],
                        $row['current'],
                        $row['bucket_1_30'],
                        $row['bucket_31_60'],
                        $row['bucket_61_90'],
                        $row['bucket_91_plus'],
                        $row['total_overdue'],
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Customer',
                    'Customer No',
                    'Phone',
                    'Loan No',
                    'Amount',
                    'Outstanding Balance',
                    'Disbursed Date',
                    'Expiry',
                    'Branch',
                    'Loan Officer',
                    'Current',
                    '1-30 Days',
                    '31-60 Days',
                    '61-90 Days',
                    '91+ Days',
                    'Total Overdue',
                ];
            }

            public function title(): string
            {
                return 'Loan Aging Report';
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'loan_aging_report_' . $asOfDate . '.xlsx');
    }

    /**
     * Export Loan Aging Report to PDF
     */
    private function exportLoanAgingToPdf($agingData, $asOfDate, $branchId = null, $loanOfficerId = null)
    {
        $branch = $branchId ? Branch::find($branchId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;
        $company = Company::first(); // Get the first company record

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('loans.reports.loan_aging_pdf', [
            'agingData' => $agingData,
            'asOfDate' => $asOfDate,
            'branch' => $branch,
            'loanOfficer' => $loanOfficer,
            'company' => $company,
        ]);

        // Set PDF to landscape orientation
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('loan_aging_report_' . $asOfDate . '.pdf');
    }

    public function loanAgingInstallmentReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $loanOfficerId = $request->get('loan_officer_id');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $branch = $branchId ? Branch::find($branchId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;

        // Get aging data for installments
        $agingData = $this->getInstallmentAgingData($asOfDate, $branchId, $loanOfficerId);

        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();

        return view('loans.reports.loan_aging_installment', compact(
            'agingData', 'asOfDate', 'branch', 'loanOfficer', 'branches', 'loanOfficers'
        ));
    }

    public function exportLoanAgingInstallmentToExcel(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $loanOfficerId = $request->get('loan_officer_id');

        $agingData = $this->getInstallmentAgingData($asOfDate, $branchId, $loanOfficerId);

        return Excel::download(new class($agingData) implements FromArray, WithHeadings {
            private $agingData;

            public function __construct($agingData)
            {
                $this->agingData = $agingData;
            }

            public function array(): array
            {
                return collect($this->agingData)->map(function ($row) {
                    return [
                        $row['customer'],
                        $row['customer_no'],
                        $row['phone'],
                        $row['loan_no'],
                        $row['amount'],
                        $row['installment_amount'],
                        $row['disbursed_no'],
                        $row['expiry'],
                        $row['branch'],
                        $row['loan_officer'],
                        $row['current'],
                        $row['bucket_1_30'],
                        $row['bucket_31_60'],
                        $row['bucket_61_90'],
                        $row['bucket_91_plus'],
                        $row['total_overdue']
                    ];
                })->toArray();
            }

            public function headings(): array
            {
                return [
                    'Customer',
                    'Customer No',
                    'Phone',
                    'Loan No',
                    'Loan Amount',
                    'Installment Amount',
                    'Disbursed Date',
                    'Expiry',
                    'Branch',
                    'Loan Officer',
                    'Current',
                    '1-30 Days',
                    '31-60 Days',
                    '61-90 Days',
                    '91+ Days',
                    'Total Due Principal'
                ];
            }
        }, 'loan_aging_installment_report_' . $asOfDate . '.xlsx');
    }

    public function exportLoanAgingInstallmentToPdf(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $loanOfficerId = $request->get('loan_officer_id');

        $branch = $branchId ? Branch::find($branchId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;
        $company = Company::first();

        $agingData = $this->getInstallmentAgingData($asOfDate, $branchId, $loanOfficerId);

        $pdf = PDF::loadView('loans.reports.loan_aging_installment_pdf', compact(
            'agingData', 'asOfDate', 'branch', 'loanOfficer', 'company'
        ));

        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('loan_aging_installment_report_' . $asOfDate . '.pdf');
    }

    private function getInstallmentAgingData($asOfDate, $branchId = null, $loanOfficerId = null)
    {
        $user = auth()->user();
        $company = $user->company;

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $query = Loan::with(['customer', 'branch', 'loanOfficer', 'schedule' => function($q) use ($asOfDate) {
            $q->where('due_date', '<=', $asOfDate);
        }, 'schedule.repayments'])
            ->whereIn('branch_id', $assignedBranchIds);

        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }

        if ($loanOfficerId) {
            $query->where('loan_officer_id', $loanOfficerId);
        }

        $loans = $query->get();

        $agingData = [];

        foreach ($loans as $loan) {
            $overdueSchedules = $loan->schedule;

            if ($overdueSchedules->isEmpty()) {
                continue;
            }

            $current = 0;
            $bucket_1_30 = 0;
            $bucket_31_60 = 0;
            $bucket_61_90 = 0;
            $bucket_91_plus = 0;

            foreach ($overdueSchedules as $schedule) {
                $dueDate = Carbon::parse($schedule->due_date);
                $asOfDateCarbon = Carbon::parse($asOfDate);

                // Calculate days past due
                $daysPastDue = $asOfDateCarbon->diffInDays($dueDate, false);

                // Calculate outstanding principal for this schedule
                $principalPaid = $schedule->repayments->sum('principal');
                $principalDue = $schedule->principal - $principalPaid;

                if ($principalDue <= 0) continue;

                if ($daysPastDue < 0) {
                    // Future installments
                    $current += $principalDue;
                } elseif ($daysPastDue <= 30) {
                    $bucket_1_30 += $principalDue;
                } elseif ($daysPastDue <= 60) {
                    $bucket_31_60 += $principalDue;
                } elseif ($daysPastDue <= 90) {
                    $bucket_61_90 += $principalDue;
                } else {
                    $bucket_91_plus += $principalDue;
                }
            }

            $totalOverdue = $current + $bucket_1_30 + $bucket_31_60 + $bucket_61_90 + $bucket_91_plus;

            if ($current > 0 || $totalOverdue > 0) {
                $agingData[] = [
                    'customer' => $loan->customer->name ?? 'N/A',
                    'customer_no' => $loan->customer->customerNo ?? 'N/A',
                    'phone' => $loan->customer->phone1 ?? 'N/A',
                    'loan_no' => $loan->loanNo ?? 'N/A',
                    'amount' => $loan->amount,
                    'installment_amount' => $loan->installment_amount ?? ($loan->amount / $loan->period),
                    'disbursed_no' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                    'expiry' => $loan->last_repayment_date ? Carbon::parse($loan->last_repayment_date)->format('d-m-Y') : 'N/A',
                    'branch' => $loan->branch->name ?? 'N/A',
                    'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                    'current' => $current,
                    'bucket_1_30' => $bucket_1_30,
                    'bucket_31_60' => $bucket_31_60,
                    'bucket_61_90' => $bucket_61_90,
                    'bucket_91_plus' => $bucket_91_plus,
                    'total_overdue' => $totalOverdue,
                ];
            }
        }

        return $agingData;
    }

    /**
     * Loan Arrears Report - Shows loans with overdue payments
     */
    public function loanArrearsReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();

        // If this is an AJAX request for DataTables
        if ($request->ajax()) {
            return $this->getArrearsDataForDataTables($request);
        }

        // Load initial arrears data
        $arrearsData = $this->getArrearsData($branchId, $groupId, $loanOfficerId);

        return view('loans.reports.loan_arrears', compact('branches', 'groups', 'loanOfficers', 'branchId', 'groupId', 'loanOfficerId', 'arrearsData'));
    }

    /**
     * Get arrears data for AJAX DataTables
     */
    public function getArrearsDataForDataTables(Request $request)
    {
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        $arrearsData = $this->getArrearsData($branchId, $groupId, $loanOfficerId);

        return response()->json([
            'data' => $arrearsData,
            'recordsTotal' => count($arrearsData),
            'recordsFiltered' => count($arrearsData),
        ]);
    }

    /**
     * Export Loan Arrears Report to Excel
     */
    public function exportLoanArrearsToExcel(Request $request)
    {
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        $arrearsData = $this->getArrearsData($branchId, $groupId, $loanOfficerId);

        $data = [
            'arrears_data' => $arrearsData,
            'branch_name' => $branchId ? Branch::find($branchId)->name : 'All Branches',
            'group_name' => $groupId ? Group::find($groupId)->name : 'All Groups',
            'loan_officer_name' => $loanOfficerId ? User::find($loanOfficerId)->name : 'All Officers',
            'generated_date' => Carbon::now()->format('d-m-Y H:i:s'),
        ];

        return Excel::download(new \App\Exports\LoanArrearsExport($data), 'loan_arrears_report_' . date('Y_m_d') . '.xlsx');
    }

    /**
     * Export Loan Arrears Report to PDF
     */
    public function exportLoanArrearsToPdf(Request $request)
    {
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        $arrearsData = $this->getArrearsData($branchId, $groupId, $loanOfficerId);

        // Get company details
        $company = Company::first();
        $branch = $branchId ? Branch::find($branchId) : null;
        $group = $groupId ? Group::find($groupId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;

        $data = [
            'arrears_data' => $arrearsData,
            'company' => $company,
            'branch' => $branch,
            'group' => $group,
            'loan_officer' => $loanOfficer,
            'generated_date' => Carbon::now()->format('d-m-Y H:i:s'),
            'branch_name' => $branch ? $branch->name : 'All Branches',
            'group_name' => $group ? $group->name : 'All Groups',
            'loan_officer_name' => $loanOfficer ? $loanOfficer->name : 'All Officers',
        ];

        $pdf = PDF::loadView('loans.reports.loan_arrears_pdf', $data)
                  ->setPaper('A3', 'landscape');

        return $pdf->download('loan_arrears_report_' . date('Y_m_d') . '.pdf');
    }

    /**
     * Get arrears data for loans that are overdue
     */
    private function getArrearsData($branchId = null, $groupId = null, $loanOfficerId = null)
    {
        $user = auth()->user();
        $company = $user->company;
        $today = Carbon::now();

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $loansQuery = Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule.repayments'])
                          ->where('status', 'active')
                          ->whereIn('branch_id', $assignedBranchIds);

        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($groupId) {
            $loansQuery->where('group_id', $groupId);
        }

        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
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
                $dueDate = Carbon::parse($schedule->due_date);

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
                    'customer' => $loan->customer->name ?? 'N/A',
                    'customer_no' => $loan->customer->customerNo ?? 'N/A',
                    'phone' => $loan->customer->phone1 ?? 'N/A',
                    'loan_no' => $loan->loanNo ?? 'N/A',
                    'loan_amount' => $loan->amount,
                    'disbursed_date' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                    'branch' => $loan->branch->name ?? 'N/A',
                    'group' => $loan->group->name ?? 'N/A',
                    'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                    'arrears_amount' => $totalArrears,
                    'days_in_arrears' => $daysInArrears,
                    'first_overdue_date' => $firstOverdueDate ? $firstOverdueDate->format('d-m-Y') : 'N/A',
                    'overdue_schedules_count' => count($overdueSchedules),
                    'arrears_severity' => $this->getArrearsSeverity($daysInArrears),
                ];
            }
        }

        // Sort by days in arrears (highest first)
        usort($arrearsData, function($a, $b) {
            return $b['days_in_arrears'] - $a['days_in_arrears'];
        });

        return $arrearsData;
    }

    /**
     * Determine arrears severity based on days overdue
     */
    private function getArrearsSeverity($daysInArrears)
    {
        if ($daysInArrears <= 30) {
            return 'Low';
        } elseif ($daysInArrears <= 60) {
            return 'Medium';
        } elseif ($daysInArrears <= 90) {
            return 'High';
        } else {
            return 'Critical';
        }
    }

    /**
     * Expected vs Collected Report - Shows expected amounts vs actual collections for a period
     */
    public function expectedVsCollectedReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();

        // Get the expected vs collected data
        $reportData = $this->getExpectedVsCollectedData($startDate, $endDate, $branchId, $groupId, $loanOfficerId);

        return view('loans.reports.expected_vs_collected', compact(
            'branches', 'groups', 'loanOfficers', 'startDate', 'endDate',
            'branchId', 'groupId', 'loanOfficerId', 'reportData'
        ));
    }

    /**
     * Export Expected vs Collected Report to Excel
     */
    public function exportExpectedVsCollectedToExcel(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        $reportData = $this->getExpectedVsCollectedData($startDate, $endDate, $branchId, $groupId, $loanOfficerId);

        $data = [
            'report_data' => $reportData,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'branch_name' => $branchId ? Branch::find($branchId)->name : 'All Branches',
            'group_name' => $groupId ? Group::find($groupId)->name : 'All Groups',
            'loan_officer_name' => $loanOfficerId ? User::find($loanOfficerId)->name : 'All Officers',
            'generated_date' => Carbon::now()->format('d-m-Y H:i:s'),
        ];

        return Excel::download(new \App\Exports\ExpectedVsCollectedExport($data), 'expected_vs_collected_report_' . $startDate . '_to_' . $endDate . '.xlsx');
    }

    /**
     * Export Expected vs Collected Report to PDF
     */
    public function exportExpectedVsCollectedToPdf(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');

        $reportData = $this->getExpectedVsCollectedData($startDate, $endDate, $branchId, $groupId, $loanOfficerId);

        // Get company and filter details
        $company = Company::first();
        $branch = $branchId ? Branch::find($branchId) : null;
        $group = $groupId ? Group::find($groupId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;

        $data = [
            'report_data' => $reportData,
            'company' => $company,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'branch' => $branch,
            'group' => $group,
            'loan_officer' => $loanOfficer,
            'branch_name' => $branch ? $branch->name : 'All Branches',
            'group_name' => $group ? $group->name : 'All Groups',
            'loan_officer_name' => $loanOfficer ? $loanOfficer->name : 'All Officers',
            'generated_date' => Carbon::now()->format('d-m-Y H:i:s'),
        ];

        $pdf = PDF::loadView('loans.reports.expected_vs_collected_pdf', $data)
                  ->setPaper('A3', 'landscape');

        return $pdf->download('expected_vs_collected_report_' . $startDate . '_to_' . $endDate . '.pdf');
    }

    /**
     * Get expected vs collected data for a specific period
     */
    private function getExpectedVsCollectedData($startDate, $endDate, $branchId = null, $groupId = null, $loanOfficerId = null)
    {
        $user = auth()->user();
        $company = $user->company;

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $loansQuery = Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule.repayments'])
                          ->where('status', 'active')
                          ->whereIn('branch_id', $assignedBranchIds);

        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($groupId) {
            $loansQuery->where('group_id', $groupId);
        }

        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }

        $loans = $loansQuery->get();
        $reportData = [];

        foreach ($loans as $loan) {
            $expectedPrincipal = 0;
            $expectedInterest = 0;
            $expectedFees = 0;
            $expectedPenalty = 0;
            $expectedTotal = 0;

            $collectedPrincipal = 0;
            $collectedInterest = 0;
            $collectedFees = 0;
            $collectedPenalty = 0;
            $collectedTotal = 0;

            // Get schedules that fall within the date range
            $schedulesInPeriod = $loan->schedule->filter(function($schedule) use ($startDate, $endDate) {
                $dueDate = Carbon::parse($schedule->due_date);
                return $dueDate->between(Carbon::parse($startDate), Carbon::parse($endDate));
            });

            foreach ($schedulesInPeriod as $schedule) {
                // Calculate expected amounts from schedule
                $expectedPrincipal += $schedule->principal ?? 0;
                $expectedInterest += $schedule->interest ?? 0;
                $expectedFees += $schedule->fee_amount ?? 0;
                $expectedPenalty += $schedule->penalty_amount ?? 0;

                // Calculate collected amounts from repayments for this schedule
                $repayments = $schedule->repayments;
                foreach ($repayments as $repayment) {
                    $paymentDate = Carbon::parse($repayment->payment_date);
                    // Only count repayments made within the period
                    if ($paymentDate->between(Carbon::parse($startDate), Carbon::parse($endDate))) {
                        $collectedPrincipal += $repayment->principal ?? 0;
                        $collectedInterest += $repayment->interest ?? 0;
                        $collectedFees += $repayment->fee_amount ?? 0;
                        $collectedPenalty += $repayment->penalt_amount ?? 0;
                    }
                }
            }

            $expectedTotal = $expectedPrincipal + $expectedInterest + $expectedFees + $expectedPenalty;
            $collectedTotal = $collectedPrincipal + $collectedInterest + $collectedFees + $collectedPenalty;

            // Only include loans that have expected amounts in the period
            if ($expectedTotal > 0) {
                $variance = $collectedTotal - $expectedTotal;
                $collectionRate = $expectedTotal > 0 ? ($collectedTotal / $expectedTotal) * 100 : 0;

                $reportData[] = [
                    'customer' => $loan->customer->name ?? 'N/A',
                    'customer_no' => $loan->customer->customerNo ?? 'N/A',
                    'phone' => $loan->customer->phone1 ?? 'N/A',
                    'loan_no' => $loan->loanNo ?? 'N/A',
                    'loan_amount' => $loan->amount,
                    'disbursed_date' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                    'branch' => $loan->branch->name ?? 'N/A',
                    'group' => $loan->group->name ?? 'N/A',
                    'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                    'expected_principal' => $expectedPrincipal,
                    'expected_interest' => $expectedInterest,
                    'expected_fees' => $expectedFees,
                    'expected_penalty' => $expectedPenalty,
                    'expected_total' => $expectedTotal,
                    'collected_principal' => $collectedPrincipal,
                    'collected_interest' => $collectedInterest,
                    'collected_fees' => $collectedFees,
                    'collected_penalty' => $collectedPenalty,
                    'collected_total' => $collectedTotal,
                    'variance' => $variance,
                    'collection_rate' => round($collectionRate, 2),
                    'collection_status' => $this->getCollectionStatus($collectionRate),
                ];
            }
        }

        // Sort by collection rate (lowest first to highlight problem loans)
        usort($reportData, function($a, $b) {
            return $a['collection_rate'] <=> $b['collection_rate'];
        });

        return $reportData;
    }

    /**
     * Determine collection status based on collection rate
     */
    private function getCollectionStatus($collectionRate)
    {
        if ($collectionRate >= 100) {
            return 'Excellent';
        } elseif ($collectionRate >= 80) {
            return 'Good';
        } elseif ($collectionRate >= 60) {
            return 'Fair';
        } elseif ($collectionRate >= 40) {
            return 'Poor';
        } else {
            return 'Critical';
        }
    }

    /**
     * Portfolio at Risk (PAR) Report - Shows loan portfolio risk analysis
     */
    public function portfolioAtRiskReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        $asOfDate = $request->input('as_of_date', Carbon::now()->toDateString());
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');
        $parDays = $request->input('par_days', 30); // Default to PAR 30

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                if ($branchId !== 'all') {
                    $query->whereHas('branches', function ($q) use ($branchId) {
                        $q->where('branches.id', $branchId);
                    });
                }
            })
            ->get();

        // Get the PAR data
        $parData = $this->getPortfolioAtRiskData($asOfDate, $branchId, $groupId, $loanOfficerId, $parDays);

        return view('loans.reports.portfolio_at_risk', compact(
            'branches', 'groups', 'loanOfficers', 'asOfDate',
            'branchId', 'groupId', 'loanOfficerId', 'parDays', 'parData'
        ));
    }

    /**
     * Export Portfolio at Risk Report to Excel
     */
    public function exportPortfolioAtRiskToExcel(Request $request)
    {
        $asOfDate = $request->input('as_of_date');
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');
        $parDays = $request->input('par_days', 30);

        $parData = $this->getPortfolioAtRiskData($asOfDate, $branchId, $groupId, $loanOfficerId, $parDays);

        $data = [
            'par_data' => $parData,
            'as_of_date' => $asOfDate,
            'par_days' => $parDays,
            'branch_name' => $branchId ? Branch::find($branchId)->name : 'All Branches',
            'group_name' => $groupId ? Group::find($groupId)->name : 'All Groups',
            'loan_officer_name' => $loanOfficerId ? User::find($loanOfficerId)->name : 'All Officers',
            'generated_date' => Carbon::now()->format('d-m-Y H:i:s'),
        ];

        return Excel::download(new \App\Exports\PortfolioAtRiskExport($data), 'portfolio_at_risk_report_' . $asOfDate . '.xlsx');
    }

    /**
     * Export Portfolio at Risk Report to PDF
     */
    public function exportPortfolioAtRiskToPdf(Request $request)
    {
        $asOfDate = $request->input('as_of_date');
        $branchId = $request->input('branch_id');
        $groupId = $request->input('group_id');
        $loanOfficerId = $request->input('loan_officer_id');
        $parDays = $request->input('par_days', 30);

        $parData = $this->getPortfolioAtRiskData($asOfDate, $branchId, $groupId, $loanOfficerId, $parDays);

        // Get company and filter details
        $company = Company::first();
        $branch = $branchId ? Branch::find($branchId) : null;
        $group = $groupId ? Group::find($groupId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;

        $data = [
            'par_data' => $parData,
            'company' => $company,
            'as_of_date' => $asOfDate,
            'par_days' => $parDays,
            'branch' => $branch,
            'group' => $group,
            'loan_officer' => $loanOfficer,
            'branch_name' => $branch ? $branch->name : 'All Branches',
            'group_name' => $group ? $group->name : 'All Groups',
            'loan_officer_name' => $loanOfficer ? $loanOfficer->name : 'All Officers',
            'generated_date' => Carbon::now()->format('d-m-Y H:i:s'),
        ];

        $pdf = PDF::loadView('loans.reports.portfolio_at_risk_pdf', $data)
                  ->setPaper('A3', 'landscape');

        return $pdf->download('portfolio_at_risk_report_' . $asOfDate . '.pdf');
    }

    /**
     * Loan Portfolio Tracking Report - Filters and view
     */
    public function portfolioTrackingReport(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $groupBy = $request->get('group_by', 'day'); // day, week, month

        // Get user's assigned branches
        $user = auth()->user();
        $userBranches = $user->branches()->active()->get();

        // If user has access to multiple branches, add "All Branches" option
        $branches = $userBranches;
        if ($userBranches->count() > 1) {
            $branches = $userBranches->prepend((object)[
                'id' => 'all',
                'name' => 'All Branches',
                'branch_name' => 'All Branches'
            ]);
        }

        $groups = \App\Models\Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();

        $showData = $request->has('from_date') || $request->has('to_date') || $request->has('branch_id') || $request->has('group_id') || $request->has('loan_officer_id');
        $trackingData = [];
        if ($showData) {
            $trackingData = $this->buildPortfolioTrackingData($fromDate, $toDate, $branchId, $groupId, $loanOfficerId, $groupBy);
        }

        return view('loans.reports.portfolio_tracking', compact(
            'fromDate','toDate','branchId','groupId','loanOfficerId','groupBy','branches','groups','loanOfficers','showData','trackingData'
        ));
    }

    /**
     * Export Portfolio Tracking to Excel
     */
    public function exportPortfolioTrackingToExcel(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $groupBy = $request->get('group_by', 'day');

        $rows = $this->buildPortfolioTrackingData($fromDate, $toDate, $branchId, $groupId, $loanOfficerId, $groupBy);

        $heading = [
            'Group',
            $groupBy !== 'day' ? 'Date Range' : null,
            'Customer Name', 'Loan Officer', 'Loan Product', 'Loan Account No.', 'Disbursement Date', 'Maturity Date',
            'Amount Disbursed', 'Interest', 'Total Amount (Principal + Interest)', 'Principal Paid', 'Interest Paid', 'Penalties Paid',
            'Outstanding Principal', 'Outstanding Interest', 'Amount Overdue', 'Days in Arrears', 'Loan Status'
        ];
        $heading = array_filter($heading); // Remove null values

        $data = [
            'headings' => $heading,
            'rows' => array_map(function($r) use ($groupBy) {
                $values = [
                    $r['group'],
                    $r['customer_name'],
                    $r['loan_officer'],
                    $r['loan_product'],
                    $r['loan_account_no'],
                    $r['disbursement_date'],
                    $r['maturity_date'],
                    $r['amount_disbursed'],
                    $r['interest'],
                    $r['total_amount'],
                    $r['principal_paid'],
                    $r['interest_paid'],
                    $r['penalties_paid'],
                    $r['outstanding_principal'],
                    $r['outstanding_interest'],
                    $r['amount_overdue'],
                    $r['days_in_arrears'],
                    $r['loan_status']
                ];

                // Insert date range if not day grouping
                if ($groupBy !== 'day') {
                    array_splice($values, 1, 0, [$r['date_range'] ?? '']);
                }

                return $values;
            }, $rows)
        ];

        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\GenericArrayExport($data), 'loan_portfolio_tracking_'.$fromDate.'_'.$toDate.'.xlsx');
    }

    /**
     * Export Portfolio Tracking to PDF
     */
    public function exportPortfolioTrackingToPdf(Request $request)
    {
        $fromDate = $request->get('from_date', now()->startOfMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $groupBy = $request->get('group_by', 'day');

        $rows = $this->buildPortfolioTrackingData($fromDate, $toDate, $branchId, $groupId, $loanOfficerId, $groupBy);

        $company = \App\Models\Company::first();
        $pdf = \PDF::loadView('loans.reports.portfolio_tracking_pdf', [
            'rows' => $rows,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'groupBy' => $groupBy,
            'company' => $company,
        ])->setPaper('A3', 'landscape');

        return $pdf->download('loan_portfolio_tracking_'.$fromDate.'_'.$toDate.'.pdf');
    }

    /**
     * Build tracking data rows according to filters
     */
    private function buildPortfolioTrackingData($fromDate, $toDate, $branchId = null, $groupId = null, $loanOfficerId = null, $groupBy = 'day')
    {
        $from = \Carbon\Carbon::parse($fromDate)->startOfDay();
        $to = \Carbon\Carbon::parse($toDate)->endOfDay();

        // Get user's assigned branches
        $user = auth()->user();
        $userBranchIds = $user->branches()->pluck('branches.id')->toArray();

        $loans = \App\Models\Loan::with(['customer','branch','group','loanOfficer','product','schedule.repayments','repayments'])
            ->whereIn('branch_id', $userBranchIds) // Filter by user's assigned branches
            ->when($branchId && $branchId !== 'all', fn($q) => $q->where('branch_id', $branchId))
            ->when($groupId, fn($q) => $q->where('group_id', $groupId))
            ->when($loanOfficerId, fn($q) => $q->where('loan_officer_id', $loanOfficerId))
            ->whereBetween('disbursed_on', [$from->toDateString(), $to->toDateString()])
            ->get();

        $rows = [];
        $groupedData = [];

        foreach ($loans as $loan) {
            // Basic amounts
            $disbursedAmount = $loan->amount ?? 0;
            $interestAmount = $loan->interest_amount ?? 0;

            $totalDue = $loan->amount_total ?? ($disbursedAmount + $interestAmount);
            if ($totalDue == 0 && $loan->schedule->count() > 0) {
                $totalDue = $loan->schedule->sum(function($s){ return ($s->principal ?? 0) + ($s->interest ?? 0) + ($s->fee_amount ?? 0); });
            }

            // Paid breakdown
            $principalPaid = 0; $interestPaid = 0; $penaltiesPaid = 0;
            if ($loan->schedule->count() > 0) {
                foreach ($loan->schedule as $s) {
                    $principalPaid += $s->repayments->sum('principal');
                    $interestPaid += $s->repayments->sum('interest');
                    $penaltiesPaid += $s->repayments->sum('penalt_amount');
                }
            } else {
                $principalPaid = $loan->repayments->sum('principal');
                $interestPaid = $loan->repayments->sum('interest');
                $penaltiesPaid = $loan->repayments->sum('penalt_amount');
            }

            $outstandingPrincipal = max(0, ($loan->amount ?? 0) - $principalPaid);
            $outstandingInterest = max(0, ($loan->interest_amount ?? 0) - $interestPaid);

            // Overdue and days in arrears
            $amountOverdue = 0; $daysInArrears = 0;
            if ($loan->schedule->count() > 0) {
                foreach ($loan->schedule as $s) {
                    $due = ($s->principal ?? 0) + ($s->interest ?? 0) + ($s->fee_amount ?? 0);
                    $paid = $s->repayments->sum('amount');
                    $remain = max(0, $due - $paid);
                    if ($remain > 0 && \Carbon\Carbon::parse($s->due_date)->lte(now())) {
                        $amountOverdue += $remain;
                        $daysInArrears = max($daysInArrears, now()->diffInDays(\Carbon\Carbon::parse($s->due_date)));
                    }
                }
            }

            // Group key and date range
            $disbursedDate = \Carbon\Carbon::parse($loan->disbursed_on);
            $groupKey = match($groupBy) {
                'week' => $disbursedDate->startOfWeek()->format('Y-m-d'),
                'month' => $disbursedDate->format('Y-m'),
                default => $disbursedDate->format('Y-m-d')
            };

            // Calculate date range for group
            $dateRange = match($groupBy) {
                'week' => $disbursedDate->startOfWeek()->format('M d') . ' - ' . $disbursedDate->endOfWeek()->format('M d, Y'),
                'month' => $disbursedDate->format('F Y'),
                default => $disbursedDate->format('M d, Y')
            };

            $loanData = [
                'group' => $groupKey,
                'date_range' => $dateRange,
                'customer_name' => $loan->customer->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'loan_product' => $loan->product->name ?? 'N/A',
                'loan_account_no' => $loan->loanNo ?? '-',
                'disbursement_date' => $loan->disbursed_on ? \Carbon\Carbon::parse($loan->disbursed_on)->format('Y-m-d') : '-',
                'maturity_date' => $loan->last_repayment_date ? \Carbon\Carbon::parse($loan->last_repayment_date)->format('Y-m-d') : '-',
                'amount_disbursed' => round($disbursedAmount, 2),
                'interest' => round($interestAmount, 2),
                'total_amount' => round($totalDue, 2),
                'principal_paid' => round($principalPaid, 2),
                'interest_paid' => round($interestPaid, 2),
                'penalties_paid' => round($penaltiesPaid, 2),
                'outstanding_principal' => round($outstandingPrincipal, 2),
                'outstanding_interest' => round($outstandingInterest, 2),
                'amount_overdue' => round($amountOverdue, 2),
                'days_in_arrears' => $daysInArrears,
                'loan_status' => $loan->status ?? 'N/A',
            ];

            // Group data for summary rows
            if (!isset($groupedData[$groupKey])) {
                $groupedData[$groupKey] = [
                    'date_range' => $dateRange,
                    'loans' => [],
                    'summary' => [
                        'total_loans' => 0,
                        'total_disbursed' => 0,
                        'total_interest' => 0,
                        'total_amount' => 0,
                        'total_principal_paid' => 0,
                        'total_interest_paid' => 0,
                        'total_penalties_paid' => 0,
                        'total_outstanding_principal' => 0,
                        'total_outstanding_interest' => 0,
                        'total_overdue' => 0,
                        'max_days_arrears' => 0,
                    ]
                ];
            }

            $groupedData[$groupKey]['loans'][] = $loanData;
            $groupedData[$groupKey]['summary']['total_loans']++;
            $groupedData[$groupKey]['summary']['total_disbursed'] += $disbursedAmount;
            $groupedData[$groupKey]['summary']['total_interest'] += $interestAmount;
            $groupedData[$groupKey]['summary']['total_amount'] += $totalDue;
            $groupedData[$groupKey]['summary']['total_principal_paid'] += $principalPaid;
            $groupedData[$groupKey]['summary']['total_interest_paid'] += $interestPaid;
            $groupedData[$groupKey]['summary']['total_penalties_paid'] += $penaltiesPaid;
            $groupedData[$groupKey]['summary']['total_outstanding_principal'] += $outstandingPrincipal;
            $groupedData[$groupKey]['summary']['total_outstanding_interest'] += $outstandingInterest;
            $groupedData[$groupKey]['summary']['total_overdue'] += $amountOverdue;
            $groupedData[$groupKey]['summary']['max_days_arrears'] = max($groupedData[$groupKey]['summary']['max_days_arrears'], $daysInArrears);
        }

        // Build final rows with grouping
        foreach ($groupedData as $groupKey => $groupData) {
            // Add summary row first if not day grouping
            if ($groupBy !== 'day') {
                $rows[] = [
                    'group' => $groupKey,
                    'date_range' => $groupData['date_range'],
                    'customer_name' => "SUMMARY ({$groupData['summary']['total_loans']} loans)",
                    'loan_officer' => '',
                    'loan_product' => '',
                    'loan_account_no' => '',
                    'disbursement_date' => '',
                    'maturity_date' => '',
                    'amount_disbursed' => round($groupData['summary']['total_disbursed'], 2),
                    'interest' => round($groupData['summary']['total_interest'], 2),
                    'total_amount' => round($groupData['summary']['total_amount'], 2),
                    'principal_paid' => round($groupData['summary']['total_principal_paid'], 2),
                    'interest_paid' => round($groupData['summary']['total_interest_paid'], 2),
                    'penalties_paid' => round($groupData['summary']['total_penalties_paid'], 2),
                    'outstanding_principal' => round($groupData['summary']['total_outstanding_principal'], 2),
                    'outstanding_interest' => round($groupData['summary']['total_outstanding_interest'], 2),
                    'amount_overdue' => round($groupData['summary']['total_overdue'], 2),
                    'days_in_arrears' => $groupData['summary']['max_days_arrears'],
                    'loan_status' => '',
                    'is_summary' => true,
                ];
            }

            // Add individual loan rows
            foreach ($groupData['loans'] as $loanData) {
                $rows[] = $loanData;
            }
        }

        // Sort by group then date
        usort($rows, function($a,$b){
            return [$a['group'],$a['disbursement_date']] <=> [$b['group'],$b['disbursement_date']];
        });

        return $rows;
    }
    /**
     * Get Portfolio at Risk data
     */
    private function getPortfolioAtRiskData($asOfDate, $branchId = null, $groupId = null, $loanOfficerId = null, $parDays = 30)
    {
        $user = auth()->user();
        $company = $user->company;
        $asOfDateCarbon = Carbon::parse($asOfDate);

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $loansQuery = Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule.repayments'])
                          ->where('status', 'active')
                          ->whereIn('branch_id', $assignedBranchIds);

        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($groupId) {
            $loansQuery->where('group_id', $groupId);
        }

        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }

        $loans = $loansQuery->get();
        $parData = [];

        foreach ($loans as $loan) {
            $outstandingBalance = 0;
            $atRiskAmount = 0;
            $daysInArrears = 0;
            $isAtRisk = false;
            $oldestOverdueDate = null;

            // Calculate outstanding balance from schedule
            $totalDue = 0;
            $totalPaid = 0;

            foreach ($loan->schedule as $schedule) {
                $scheduleDue = ($schedule->principal ?? 0) + ($schedule->interest ?? 0) + ($schedule->fee_amount ?? 0);
                $schedulePaid = $schedule->repayments->sum('amount');

                $totalDue += $scheduleDue;
                $totalPaid += $schedulePaid;
            }

            $outstandingBalance = $totalDue - $totalPaid;

            // Skip loans with no outstanding balance
            if ($outstandingBalance <= 0) {
                continue;
            }

            // Check schedules for overdue amounts
            $overdueAmount = 0;
            foreach ($loan->schedule as $schedule) {
                $dueDate = Carbon::parse($schedule->due_date);

                if ($dueDate->lte($asOfDateCarbon)) {
                    $scheduleDue = ($schedule->principal ?? 0) + ($schedule->interest ?? 0) + ($schedule->fee_amount ?? 0);
                    $schedulePaid = $schedule->repayments->sum('amount');
                    $scheduleRemaining = $scheduleDue - $schedulePaid;

                    if ($scheduleRemaining > 0) {
                        $daysPastDue = $asOfDateCarbon->diffInDays($dueDate);
                        $overdueAmount += $scheduleRemaining;

                        if ($daysPastDue >= $parDays) {
                            $isAtRisk = true;

                            if (!$oldestOverdueDate || $dueDate->lt($oldestOverdueDate)) {
                                $oldestOverdueDate = $dueDate;
                                $daysInArrears = $daysPastDue;
                            }
                        }
                    }
                }
            }

            // If loan is at risk, the entire outstanding balance is considered at risk
            $atRiskAmount = $isAtRisk ? $outstandingBalance : 0;

            // Use loan model's days_in_arrears if available, otherwise calculate from oldest overdue
            if (isset($loan->days_in_arrears) && $loan->days_in_arrears > 0) {
                $daysInArrears = $loan->days_in_arrears;
                $isAtRisk = $daysInArrears >= $parDays;
                $atRiskAmount = $isAtRisk ? $outstandingBalance : 0;
            }

            // Calculate risk metrics
            $riskPercentage = $outstandingBalance > 0 ? ($atRiskAmount / $outstandingBalance) * 100 : 0;
            $riskLevel = $this->getRiskLevel($daysInArrears);

            $parData[] = [
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? 'N/A',
                'phone' => $loan->customer->phone1 ?? 'N/A',
                'loan_no' => $loan->loanNo ?? 'N/A',
                'loan_amount' => $loan->amount,
                'disbursed_date' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'group' => $loan->group->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'outstanding_balance' => $outstandingBalance,
                'at_risk_amount' => $atRiskAmount,
                'risk_percentage' => round($riskPercentage, 2),
                'days_in_arrears' => $daysInArrears,
                'oldest_overdue_date' => $oldestOverdueDate ? $oldestOverdueDate->format('d-m-Y') : 'N/A',
                'risk_level' => $riskLevel,
                'is_at_risk' => $isAtRisk,
                'par_days' => $parDays,
            ];
        }

        // Sort by days in arrears (highest first, then by outstanding balance)
        usort($parData, function($a, $b) {
            if ($a['days_in_arrears'] == $b['days_in_arrears']) {
                return $b['outstanding_balance'] <=> $a['outstanding_balance'];
            }
            return $b['days_in_arrears'] <=> $a['days_in_arrears'];
        });

        return $parData;
    }

    /**
     * Internal Portfolio Analysis Report (Conservative Approach)
     */
    public function internalPortfolioAnalysisReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $groupId = $request->get('group_id');
        $loanOfficerId = $request->get('loan_officer_id');
        $parDays = $request->get('par_days', 30);

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                if ($branchId !== 'all') {
                    $query->whereHas('branches', function ($q) use ($branchId) {
                        $q->where('branches.id', $branchId);
                    });
                }
            })
            ->get();
        $company = Company::first();

        $analysisData = $this->getInternalPortfolioAnalysisData($asOfDate, $branchId, $groupId, $loanOfficerId, $parDays);

        return view('loans.reports.internal_portfolio_analysis', compact(
            'analysisData', 'branches', 'groups', 'loanOfficers', 'company',
            'asOfDate', 'branchId', 'groupId', 'loanOfficerId', 'parDays'
        ));
    }

    /**
     * Export Internal Portfolio Analysis to Excel
     */
    public function exportInternalPortfolioAnalysisToExcel(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $groupId = $request->get('group_id');
        $loanOfficerId = $request->get('loan_officer_id');
        $parDays = $request->get('par_days', 30);

        $analysisData = $this->getInternalPortfolioAnalysisData($asOfDate, $branchId, $groupId, $loanOfficerId, $parDays);
        $company = Company::first();

        $filters = [
            'as_of_date' => $asOfDate,
            'par_days' => $parDays,
            'branch_name' => $branchId ? Branch::find($branchId)->name : 'All Branches',
            'group_name' => $groupId ? Group::find($groupId)->name : 'All Groups',
            'loan_officer_name' => $loanOfficerId ? User::find($loanOfficerId)->name : 'All Officers',
        ];

        $filename = 'internal_portfolio_analysis_' . date('Y_m_d_His') . '.xlsx';

        return Excel::download(new InternalPortfolioAnalysisExport($analysisData, $filters, $company), $filename);
    }

    /**
     * Export Internal Portfolio Analysis to PDF
     */
    public function exportInternalPortfolioAnalysisToPdf(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $groupId = $request->get('group_id');
        $loanOfficerId = $request->get('loan_officer_id');
        $parDays = $request->get('par_days', 30);

        $analysisData = $this->getInternalPortfolioAnalysisData($asOfDate, $branchId, $groupId, $loanOfficerId, $parDays);
        $company = Company::first();

        $data = [
            'analysis_data' => $analysisData,
            'company' => $company,
            'generated_date' => now()->format('d-m-Y H:i:s'),
            'as_of_date' => $asOfDate,
            'par_days' => $parDays,
            'branch_name' => $branchId ? Branch::find($branchId)->name : 'All Branches',
            'group_name' => $groupId ? Group::find($groupId)->name : 'All Groups',
            'loan_officer_name' => $loanOfficerId ? User::find($loanOfficerId)->name : 'All Officers',
        ];

        $filename = 'internal_portfolio_analysis_' . date('Y_m_d_His') . '.pdf';

        $pdf = PDF::loadView('loans.reports.internal_portfolio_analysis_pdf', $data);
        $pdf->setPaper('A3', 'landscape');
        $pdf->setOptions([
            'margin-top' => 10,
            'margin-right' => 15,
            'margin-bottom' => 10,
            'margin-left' => 15,
        ]);

        return $pdf->download($filename);
    }

    /**
     * Get Internal Portfolio Analysis Data (Conservative Approach - Only Overdue Amounts)
     */
    private function getInternalPortfolioAnalysisData($asOfDate, $branchId = null, $groupId = null, $loanOfficerId = null, $parDays = 30)
    {
        $user = auth()->user();
        $company = $user->company;
        $asOfDateCarbon = Carbon::parse($asOfDate);

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $loansQuery = Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule.repayments'])
                          ->where('status', 'active')
                          ->whereIn('branch_id', $assignedBranchIds);

        if ($branchId && $branchId !== 'all') {
            $loansQuery->where('branch_id', $branchId);
        }

        if ($groupId) {
            $loansQuery->where('group_id', $groupId);
        }

        if ($loanOfficerId) {
            $loansQuery->where('loan_officer_id', $loanOfficerId);
        }

        $loans = $loansQuery->get();
        $analysisData = [];

        foreach ($loans as $loan) {
            $outstandingBalance = 0;
            $overdueAmount = 0;
            $currentAmount = 0;
            $daysInArrears = 0;
            $isAtRisk = false;
            $oldestOverdueDate = null;

            // Calculate outstanding balance and overdue amounts from schedule
            $totalDue = 0;
            $totalPaid = 0;

            foreach ($loan->schedule as $schedule) {
                $scheduleDue = ($schedule->principal ?? 0) + ($schedule->interest ?? 0) + ($schedule->fee_amount ?? 0);
                $schedulePaid = $schedule->repayments->sum('amount');
                $scheduleRemaining = $scheduleDue - $schedulePaid;

                $totalDue += $scheduleDue;
                $totalPaid += $schedulePaid;

                $dueDate = Carbon::parse($schedule->due_date);

                if ($scheduleRemaining > 0) {
                    if ($dueDate->lte($asOfDateCarbon)) {
                        // Overdue amounts
                        $daysPastDue = $asOfDateCarbon->diffInDays($dueDate);
                        $overdueAmount += $scheduleRemaining;

                        if ($daysPastDue >= $parDays) {
                            $isAtRisk = true;

                            if (!$oldestOverdueDate || $dueDate->lt($oldestOverdueDate)) {
                                $oldestOverdueDate = $dueDate;
                                $daysInArrears = $daysPastDue;
                            }
                        }
                    } else {
                        // Current/future amounts
                        $currentAmount += $scheduleRemaining;
                    }
                }
            }

            $outstandingBalance = $totalDue - $totalPaid;

            // Skip loans with no outstanding balance
            if ($outstandingBalance <= 0) {
                continue;
            }

            // Use loan model's days_in_arrears if available
            if (isset($loan->days_in_arrears) && $loan->days_in_arrears > 0) {
                $daysInArrears = $loan->days_in_arrears;
                $isAtRisk = $daysInArrears >= $parDays;
            }

            // Conservative approach: Only overdue amounts are at risk
            $atRiskAmount = $isAtRisk ? $overdueAmount : 0;

            // Calculate exposure ratios
            $overdueRatio = $outstandingBalance > 0 ? ($overdueAmount / $outstandingBalance) * 100 : 0;
            $riskRatio = $outstandingBalance > 0 ? ($atRiskAmount / $outstandingBalance) * 100 : 0;
            $riskLevel = $this->getRiskLevel($daysInArrears);

            $analysisData[] = [
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? 'N/A',
                'phone' => $loan->customer->phone1 ?? 'N/A',
                'loan_no' => $loan->loanNo ?? 'N/A',
                'loan_amount' => $loan->amount,
                'disbursed_date' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'group' => $loan->group->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'outstanding_balance' => $outstandingBalance,
                'overdue_amount' => $overdueAmount,
                'current_amount' => $currentAmount,
                'at_risk_amount' => $atRiskAmount,
                'overdue_ratio' => round($overdueRatio, 2),
                'risk_ratio' => round($riskRatio, 2),
                'days_in_arrears' => $daysInArrears,
                'oldest_overdue_date' => $oldestOverdueDate ? $oldestOverdueDate->format('d-m-Y') : 'N/A',
                'risk_level' => $riskLevel,
                'is_at_risk' => $isAtRisk,
                'par_days' => $parDays,
                'exposure_category' => $this->getExposureCategory($overdueRatio),
            ];
        }

        // Sort by overdue ratio (highest first, then by outstanding balance)
        usort($analysisData, function($a, $b) {
            if ($a['overdue_ratio'] == $b['overdue_ratio']) {
                return $b['outstanding_balance'] <=> $a['outstanding_balance'];
            }
            return $b['overdue_ratio'] <=> $a['overdue_ratio'];
        });

        return $analysisData;
    }

    /**
     * Get exposure category based on overdue ratio
     */
    private function getExposureCategory($overdueRatio)
    {
        if ($overdueRatio == 0) {
            return 'Current';
        } elseif ($overdueRatio <= 25) {
            return 'Low Exposure';
        } elseif ($overdueRatio <= 50) {
            return 'Medium Exposure';
        } elseif ($overdueRatio <= 75) {
            return 'High Exposure';
        } else {
            return 'Critical Exposure';
        }
    }

    /**
     * Determine risk level based on days in arrears
     */
    private function getRiskLevel($daysInArrears)
    {
        if ($daysInArrears == 0) {
            return 'Low';
        } elseif ($daysInArrears <= 30) {
            return 'Low';
        } elseif ($daysInArrears <= 60) {
            return 'Medium';
        } elseif ($daysInArrears <= 90) {
            return 'High';
        } else {
            return 'Critical';
        }
    }

    /**
     * Loan Portfolio Report - Comprehensive overview of all active loans
     */
    public function portfolioReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $status = $request->get('status') ?: 'active_completed';
        $exportType = $request->get('export_type');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();
        $company = Company::first();

        // Determine if we should show data (when form is submitted)
        $showData = $request->has('as_of_date') || $request->has('branch_id') || $request->has('group_id') ||
                   $request->has('loan_officer_id') || $request->has('status') || $request->isMethod('get');

        $portfolioData = null;
        if ($showData) {
            $portfolioData = $this->getPortfolioData($asOfDate, $branchId, $groupId, $loanOfficerId, $status);

            // Handle exports
            if ($exportType) {
                if ($exportType === 'excel') {
                    return $this->exportPortfolioToExcel($request);
                } elseif ($exportType === 'pdf') {
                    return $this->exportPortfolioToPdf($request);
                }
            }
        }

        return view('loans.reports.portfolio', compact(
            'portfolioData', 'branches', 'groups', 'loanOfficers', 'company',
            'asOfDate', 'branchId', 'groupId', 'loanOfficerId', 'status', 'showData'
        ));
    }

    /**
     * Export Portfolio Report to Excel
     */
    public function exportPortfolioToExcel(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $status = $request->get('status') ?: 'active_completed';

        $portfolioData = $this->getPortfolioData($asOfDate, $branchId, $groupId, $loanOfficerId, $status);

        $filename = 'loan_portfolio_report_' . $asOfDate . '.xlsx';

        return Excel::download(new PortfolioExport($portfolioData, $status), $filename);
    }

    /**
     * Export Portfolio Report to PDF
     */
    public function exportPortfolioToPdf(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $status = $request->get('status') ?: 'active_completed';

        $branches = Branch::all();
        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();
        $company = Company::first();

        $portfolioData = $this->getPortfolioData($asOfDate, $branchId, $groupId, $loanOfficerId, $status);

        $pdf = PDF::loadView('loans.reports.portfolio_pdf', compact(
            'portfolioData', 'branches', 'groups', 'loanOfficers', 'company',
            'asOfDate', 'branchId', 'groupId', 'loanOfficerId', 'status'
        ));

        $pdf->setPaper('A3', 'landscape');
        $pdf->setOptions(['margin-left' => 10, 'margin-right' => 10, 'margin-top' => 10, 'margin-bottom' => 10]);

        $filename = 'loan_portfolio_report_' . $asOfDate . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get Portfolio Data
     */
    private function getPortfolioData($asOfDate, $branchId = null, $groupId = null, $loanOfficerId = null, $status = 'all')
    {
        $user = auth()->user();
        $company = $user->company;

        // Get user's assigned branches
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        if (empty($assignedBranchIds)) {
            return [
                'loans' => collect([]),
                'summary' => [
                    'total_loans' => 0,
                    'total_disbursed' => 0,
                    'total_outstanding' => 0,
                    'total_paid' => 0,
                    'active_loans' => 0,
                    'completed_loans' => 0,
                    'defaulted_loans' => 0
                ]
            ];
        }

        $query = Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule', 'schedule.repayments', 'repayments'])
            ->whereIn('branch_id', $assignedBranchIds)
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })
            ->when($groupId, function($q) use ($groupId) {
                return $q->where('group_id', $groupId);
            })
            ->when($loanOfficerId, function($q) use ($loanOfficerId) {
                return $q->where('loan_officer_id', $loanOfficerId);
            });

        if ($status !== 'all') {
            if ($status === 'active_completed') {
                $query->whereIn('status', ['active', 'completed']);
            } else {
                $query->where('status', $status);
            }
        }

        $loans = $query->get();
        $portfolioData = [];

        $totalDisbursed = 0;
        $totalOutstanding = 0;
        $totalPaid = 0;
        $totalLoans = $loans->count();
        $activeLoans = 0;
        $completedLoans = 0;
        $defaultedLoans = 0;

        foreach ($loans as $loan) {
            // Calculate loan metrics using correct field names from migration
            $disbursedAmount = $loan->amount ?? 0; // Use 'amount' field for disbursed amount

            // Calculate total due - prefer loan amount_total, fallback to amount + interest
            $totalDue = $loan->amount_total ?? 0;
            if ($totalDue == 0) {
                $totalDue = $disbursedAmount + ($loan->interest_amount ?? 0);
            }

            // If we still have no total due and there's a schedule, use schedule calculation
            if ($totalDue == 0 && $loan->schedule->count() > 0) {
                $totalDue = $loan->schedule->sum(function($schedule) {
                    return $schedule->principal + $schedule->interest + ($schedule->fee_amount ?? 0);
                });
            }

            // If still no total due, fallback to disbursed amount
            if ($totalDue == 0) {
                $totalDue = $disbursedAmount;
            }

            // Calculate total repaid - try from schedule repayments first, then direct repayments
            $totalRepaid = 0;
            if ($loan->schedule->count() > 0) {
                $totalRepaid = $loan->schedule->sum(function($schedule) {
                    return $schedule->repayments->sum(function($repayment) {
                        return $repayment->amount ?? ($repayment->principal + $repayment->interest + ($repayment->fee_amount ?? 0));
                    });
                });
            } else {
                // Fallback to direct repayments
                $totalRepaid = $loan->repayments->sum('amount') ?? 0;
            }

            $outstandingAmount = max(0, $totalDue - $totalRepaid);

            // Calculate performance metrics
            $repaymentRate = $totalDue > 0 ? ($totalRepaid / $totalDue) * 100 : 0;

            // Use loan model attributes if available, otherwise calculate
            $daysInArrears = 0;
            if (method_exists($loan, 'getDaysInArrearsAttribute')) {
                $daysInArrears = $loan->days_in_arrears ?? 0;
            }
            $isInArrears = $daysInArrears > 0;

            // Loan status metrics
            if ($loan->status === 'active') $activeLoans++;
            elseif ($loan->status === 'completed') $completedLoans++;
            elseif ($loan->status === 'defaulted') $defaultedLoans++;

            $portfolioData[] = [
                'loan_id' => $loan->id,
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? $loan->customer->customer_no ?? 'N/A',
                'phone' => $loan->customer->phone1 ?? $loan->customer->phone ?? 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'group' => $loan->group->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'disbursed_amount' => $disbursedAmount,
                'outstanding_amount' => $outstandingAmount,
                'total_due' => $totalDue,
                'total_paid' => $totalRepaid,
                'repayment_rate' => $repaymentRate,
                'days_in_arrears' => $daysInArrears,
                'is_in_arrears' => $isInArrears,
                'status' => $loan->status,
                'disbursed_date' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('Y-m-d') : 'N/A', // Use 'disbursed_on'
                'maturity_date' => $loan->last_repayment_date ? Carbon::parse($loan->last_repayment_date)->format('Y-m-d') : 'N/A', // Use 'last_repayment_date' for expiry
            ];

            $totalDisbursed += $disbursedAmount;
            $totalOutstanding += $outstandingAmount;
            $totalPaid += $totalRepaid;
        }

        // Calculate summary metrics
        $overallRepaymentRate = $totalDisbursed > 0 ? ($totalPaid / $totalDisbursed) * 100 : 0;
        $portfolioAtRisk = collect($portfolioData)->where('is_in_arrears', true)->sum('outstanding_amount');
        $parRatio = $totalOutstanding > 0 ? ($portfolioAtRisk / $totalOutstanding) * 100 : 0;

        return [
            'summary' => [
                'total_loans' => $totalLoans,
                'active_loans' => $activeLoans,
                'completed_loans' => $completedLoans,
                'defaulted_loans' => $defaultedLoans,
                'total_disbursed' => $totalDisbursed,
                'total_outstanding' => $totalOutstanding,
                'total_paid' => $totalPaid,
                'overall_repayment_rate' => $overallRepaymentRate,
                'portfolio_at_risk' => $portfolioAtRisk,
                'par_ratio' => $parRatio,
            ],
            'loans' => $portfolioData,
        ];
    }

    /**
     * Loan Performance Report - Analyze loan performance metrics and repayment trends
     */
    public function performanceReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $fromDate = $request->get('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $exportType = $request->get('export_type');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();
        $company = Company::first();

        // Determine if we should show data (when form is submitted)
        $showData = $request->has('from_date') || $request->has('to_date') || $request->has('branch_id') ||
                   $request->has('group_id') || $request->has('loan_officer_id') || $request->isMethod('get');

        $performanceData = null;
        if ($showData) {
            $performanceData = $this->getPerformanceData($fromDate, $toDate, $branchId, $groupId, $loanOfficerId);

            // Handle exports
            if ($exportType) {
                if ($exportType === 'excel') {
                    return $this->exportPerformanceToExcel($request);
                } elseif ($exportType === 'pdf') {
                    return $this->exportPerformanceToPdf($request);
                }
            }
        }

        return view('loans.reports.performance', compact(
            'performanceData', 'branches', 'groups', 'loanOfficers', 'company',
            'fromDate', 'toDate', 'branchId', 'groupId', 'loanOfficerId', 'showData'
        ));
    }

    /**
     * Export Performance Report to Excel
     */
    public function exportPerformanceToExcel(Request $request)
    {
        $fromDate = $request->get('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;

        $performanceData = $this->getPerformanceData($fromDate, $toDate, $branchId, $groupId, $loanOfficerId);

        $filename = 'loan_performance_report_' . $fromDate . '_to_' . $toDate . '.xlsx';

        return Excel::download(new PerformanceExport($performanceData), $filename);
    }

    /**
     * Export Performance Report to PDF
     */
    public function exportPerformanceToPdf(Request $request)
    {
        $fromDate = $request->get('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->get('to_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;

        $branches = Branch::all();
        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();
        $company = Company::first();

        $performanceData = $this->getPerformanceData($fromDate, $toDate, $branchId, $groupId, $loanOfficerId);

        $pdf = PDF::loadView('loans.reports.performance_pdf', compact(
            'performanceData', 'branches', 'groups', 'loanOfficers', 'company',
            'fromDate', 'toDate', 'branchId', 'groupId', 'loanOfficerId'
        ));

        $pdf->setPaper('A3', 'landscape');
        $pdf->setOptions(['margin-left' => 10, 'margin-right' => 10, 'margin-top' => 10, 'margin-bottom' => 10]);

        $filename = 'loan_performance_report_' . $fromDate . '_to_' . $toDate . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get Performance Data
     */
    private function getPerformanceData($fromDate, $toDate, $branchId = null, $groupId = null, $loanOfficerId = null)
    {
        $user = auth()->user();
        $company = $user->company;

        // Get user's assigned branches
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        if (empty($assignedBranchIds)) {
            return [
                'loans' => collect([]),
                'summary' => [
                    'total_loans' => 0,
                    'excellent_loans' => 0,
                    'good_loans' => 0,
                    'fair_loans' => 0,
                    'poor_loans' => 0,
                    'critical_loans' => 0,
                    'total_disbursed' => 0,
                    'total_outstanding' => 0,
                    'total_repaid' => 0,
                    'loans_in_arrears' => 0,
                    'on_time_payments' => 0,
                    'late_payments' => 0,
                    'average_days_in_arrears' => 0,
                    'periodic_repayments' => 0,
                    'repayment_rate' => 0,
                    'collection_rate' => 0
                ]
            ];
        }

        $query = Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule', 'schedule.repayments'])
            ->where('status', 'active')
            ->whereIn('branch_id', $assignedBranchIds)
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })
            ->when($groupId, function($q) use ($groupId) {
                return $q->where('group_id', $groupId);
            })
            ->when($loanOfficerId, function($q) use ($loanOfficerId) {
                return $q->where('loan_officer_id', $loanOfficerId);
            });

        $loans = $query->get();
        $performanceData = [];

        // Period metrics
        $periodicRepayments = Repayment::whereBetween('payment_date', [$fromDate, $toDate])
            ->whereHas('schedule.loan', function($lq) use ($assignedBranchIds) {
                $lq->whereIn('branch_id', $assignedBranchIds);
            })
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->whereHas('schedule.loan', function($lq) use ($branchId) {
                    $lq->where('branch_id', $branchId);
                });
            })
            ->when($groupId, function($q) use ($groupId) {
                return $q->whereHas('schedule.loan', function($lq) use ($groupId) {
                    $lq->where('group_id', $groupId);
                });
            })
            ->when($loanOfficerId, function($q) use ($loanOfficerId) {
                return $q->whereHas('schedule.loan', function($lq) use ($loanOfficerId) {
                    $lq->where('loan_officer_id', $loanOfficerId);
                });
            })
            ->sum(DB::raw('principal + interest + COALESCE(fee_amount, 0) + COALESCE(penalt_amount, 0)'));

        $totalLoans = $loans->count();
        $totalDisbursed = 0;
        $totalOutstanding = 0;
        $totalRepaid = 0;
        $loansInArrears = 0;
        $onTimePayments = 0;
        $latePayments = 0;
        $averageDaysInArrears = 0;
        $totalDaysInArrears = 0;

        foreach ($loans as $loan) {
            // Calculate loan metrics using correct field names from migration
            $disbursedAmount = $loan->amount ?? 0; // Use 'amount' field for disbursed amount
            $totalDue = $loan->schedule->sum(function($schedule) {
                return $schedule->principal + $schedule->interest + ($schedule->fee_amount ?? 0);
            });
            $totalPaid = $loan->schedule->sum(function($schedule) {
                return $schedule->repayments->sum(function($repayment) {
                    return $repayment->principal + $repayment->interest + ($repayment->fee_amount ?? 0) + ($repayment->penalt_amount ?? 0);
                });
            });
            $outstandingAmount = $totalDue - $totalPaid;

            // Performance metrics
            $daysInArrears = $loan->days_in_arrears ?? 0;
            $isInArrears = $daysInArrears > 0;
            $repaymentRate = $totalDue > 0 ? ($totalPaid / $totalDue) * 100 : 0;

            if ($isInArrears) {
                $loansInArrears++;
                $totalDaysInArrears += $daysInArrears;
            }

            // Payment performance analysis
            $schedulePayments = $loan->schedule()->whereHas('repayments', function($q) use ($fromDate, $toDate) {
                $q->whereBetween('payment_date', [$fromDate, $toDate]);
            })->get();

            foreach ($schedulePayments as $schedule) {
                $repayments = $schedule->repayments()->whereBetween('payment_date', [$fromDate, $toDate])->get();
                foreach ($repayments as $repayment) {
                    if ($repayment->payment_date <= $schedule->due_date) {
                        $onTimePayments++;
                    } else {
                        $latePayments++;
                    }
                }
            }

            $performanceData[] = [
                'loan_id' => $loan->id,
                'customer' => $loan->customer->name ?? 'N/A',
                'customer_no' => $loan->customer->customerNo ?? 'N/A',
                'branch' => $loan->branch->name ?? 'N/A',
                'group' => $loan->group->name ?? 'N/A',
                'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                'disbursed_amount' => $disbursedAmount,
                'outstanding_amount' => $outstandingAmount,
                'total_paid' => $totalPaid,
                'repayment_rate' => $repaymentRate,
                'days_in_arrears' => $daysInArrears,
                'is_in_arrears' => $isInArrears,
                'performance_grade' => $this->getPerformanceGrade($repaymentRate, $daysInArrears),
                'risk_category' => $this->getRiskCategory($daysInArrears),
            ];

            $totalDisbursed += $disbursedAmount;
            $totalOutstanding += $outstandingAmount;
            $totalRepaid += $totalPaid;
        }

        // Calculate averages and ratios
        $averageDaysInArrears = $loansInArrears > 0 ? $totalDaysInArrears / $loansInArrears : 0;
        $totalPayments = $onTimePayments + $latePayments;
        $onTimePaymentRate = $totalPayments > 0 ? ($onTimePayments / $totalPayments) * 100 : 0;
        $latePaymentRate = $totalPayments > 0 ? ($latePayments / $totalPayments) * 100 : 0;
        $arrearsRate = $totalLoans > 0 ? ($loansInArrears / $totalLoans) * 100 : 0;
        $overallRepaymentRate = $totalDisbursed > 0 ? ($totalRepaid / $totalDisbursed) * 100 : 0;

        // Calculate performance grades counts
        $excellent_loans = collect($performanceData)->where('performance_grade', 'Excellent')->count();
        $good_loans = collect($performanceData)->where('performance_grade', 'Good')->count();
        $fair_loans = collect($performanceData)->where('performance_grade', 'Fair')->count();
        $poor_loans = collect($performanceData)->where('performance_grade', 'Poor')->count();

        // Calculate average repayment rate
        $average_repayment_rate = $totalLoans > 0
            ? collect($performanceData)->avg('repayment_rate')
            : 0;

        // Calculate total collections (all time)
        $total_collections = collect($performanceData)->sum('total_paid');

        // Calculate period collections (for the selected period)
        $period_collections = $periodicRepayments;

        return [
            'summary' => [
                'total_loans' => $totalLoans,
                'excellent_loans' => $excellent_loans,
                'good_loans' => $good_loans,
                'fair_loans' => $fair_loans,
                'poor_loans' => $poor_loans,
                'average_repayment_rate' => $average_repayment_rate,
                'total_collections' => $total_collections,
                'period_collections' => $period_collections,
                'total_disbursed' => $totalDisbursed,
                'total_outstanding' => $totalOutstanding,
                'total_repaid' => $totalRepaid,
                'periodic_repayments' => $periodicRepayments,
                'loans_in_arrears' => $loansInArrears,
                'average_days_in_arrears' => $averageDaysInArrears,
                'on_time_payments' => $onTimePayments,
                'late_payments' => $latePayments,
                'on_time_payment_rate' => $onTimePaymentRate,
                'late_payment_rate' => $latePaymentRate,
                'arrears_rate' => $arrearsRate,
                'overall_repayment_rate' => $overallRepaymentRate,
            ],
            'loans' => $performanceData,
        ];
    }

    /**
     * Get Performance Grade
     */
    private function getPerformanceGrade($repaymentRate, $daysInArrears)
    {
        if ($repaymentRate >= 95 && $daysInArrears == 0) {
            return 'Excellent';
        } elseif ($repaymentRate >= 85 && $daysInArrears <= 15) {
            return 'Good';
        } elseif ($repaymentRate >= 70 && $daysInArrears <= 30) {
            return 'Fair';
        } elseif ($repaymentRate >= 50 && $daysInArrears <= 60) {
            return 'Poor';
        } else {
            return 'Critical';
        }
    }

    /**
     * Get Risk Category
     */
    private function getRiskCategory($daysInArrears)
    {
        if ($daysInArrears == 0) {
            return 'Low Risk';
        } elseif ($daysInArrears <= 30) {
            return 'Medium Risk';
        } elseif ($daysInArrears <= 90) {
            return 'High Risk';
        } else {
            return 'Critical Risk';
        }
    }

    /**
     * Delinquency Report - Track overdue loans and payment delinquencies
     */
    public function delinquencyReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;
        
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $bucket = $request->get('bucket');
        $delinquencyDays = $request->get('delinquency_days', 1); // Minimum days to be considered delinquent
        $exportType = $request->get('export_type');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();
        $company = Company::first();

        // Determine if we should show data (when form is submitted)
        $showData = $request->has('as_of_date') || $request->has('branch_id') || $request->has('group_id') ||
                   $request->has('loan_officer_id') || $request->has('bucket') || $request->isMethod('get');

        $delinquencyData = null;
        if ($showData) {
            $delinquencyData = $this->getDelinquencyData($asOfDate, $branchId, $groupId, $loanOfficerId, $delinquencyDays, $bucket);

            // Handle exports
            if ($exportType) {
                if ($exportType === 'excel') {
                    return $this->exportDelinquencyToExcel($request);
                } elseif ($exportType === 'pdf') {
                    return $this->exportDelinquencyToPdf($request);
                }
            }
        }

        return view('loans.reports.delinquency', compact(
            'delinquencyData', 'branches', 'groups', 'loanOfficers', 'company',
            'asOfDate', 'branchId', 'groupId', 'loanOfficerId', 'bucket', 'delinquencyDays', 'showData'
        ));
    }

    /**
     * Export Delinquency Report to Excel
     */
    public function exportDelinquencyToExcel(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $delinquencyDays = $request->get('delinquency_days', 1);
        $bucket = $request->get('bucket') ?: null;

        $delinquencyData = $this->getDelinquencyData($asOfDate, $branchId, $groupId, $loanOfficerId, $delinquencyDays, $bucket);

        $filename = 'delinquency_report_' . $asOfDate . '.xlsx';

        return Excel::download(new DelinquencyExport($delinquencyData), $filename);
    }

    /**
     * Export Delinquency Report to PDF
     */
    public function exportDelinquencyToPdf(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id') ?: null;
        $groupId = $request->get('group_id') ?: null;
        $loanOfficerId = $request->get('loan_officer_id') ?: null;
        $delinquencyDays = $request->get('delinquency_days', 1);
        $bucket = $request->get('bucket') ?: null;

        $branches = Branch::all();
        $groups = Group::all();
        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                $query->whereHas('branches', function ($q) use ($branchId) {
                    $q->where('branches.id', $branchId);
                });
            })
            ->get();
        $company = Company::first();

        $delinquencyData = $this->getDelinquencyData($asOfDate, $branchId, $groupId, $loanOfficerId, $delinquencyDays, $bucket);

        $pdf = PDF::loadView('loans.reports.delinquency_pdf', compact(
            'delinquencyData', 'branches', 'groups', 'loanOfficers', 'company',
            'asOfDate', 'branchId', 'groupId', 'loanOfficerId', 'delinquencyDays', 'bucket'
        ));

        $pdf->setPaper('A3', 'landscape');
        $pdf->setOptions(['margin-left' => 10, 'margin-right' => 10, 'margin-top' => 10, 'margin-bottom' => 10]);

        $filename = 'delinquency_report_' . $asOfDate . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Get Delinquency Data
     */
    private function getDelinquencyData($asOfDate, $branchId = null, $groupId = null, $loanOfficerId = null, $delinquencyDays = 1, $bucket = null)
    {
        $user = auth()->user();
        $company = $user->company;

        // Get user's assigned branches
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        if (empty($assignedBranchIds)) {
            return [
                'loans' => collect([]),
                'summary' => [
                    'total_loans' => 0,
                    'total_delinquent_loans' => 0,
                    'total_delinquent_amount' => 0,
                    'total_outstanding' => 0,
                    'delinquency_rate' => 0,
                    'bucket_1_30' => 0,
                    'bucket_31_60' => 0,
                    'bucket_61_90' => 0,
                    'bucket_91_180' => 0,
                    'bucket_180_plus' => 0
                ]
            ];
        }

        $query = Loan::with(['customer', 'branch', 'group', 'loanOfficer', 'schedule', 'schedule.repayments'])
            ->where('status', 'active')
            ->whereIn('branch_id', $assignedBranchIds)
            ->when($branchId && $branchId !== 'all', function($q) use ($branchId) {
                return $q->where('branch_id', $branchId);
            })
            ->when($groupId, function($q) use ($groupId) {
                return $q->where('group_id', $groupId);
            })
            ->when($loanOfficerId, function($q) use ($loanOfficerId) {
                return $q->where('loan_officer_id', $loanOfficerId);
            });

        $loans = $query->get();
        $delinquencyData = [];

        $totalLoans = $loans->count();
        $delinquentLoans = 0;
        $totalDelinquentAmount = 0;
        $totalOutstanding = 0;

        // Delinquency buckets
        $bucket1to30 = ['count' => 0, 'amount' => 0]; // 1-30 days
        $bucket31to60 = ['count' => 0, 'amount' => 0]; // 31-60 days
        $bucket61to90 = ['count' => 0, 'amount' => 0]; // 61-90 days
        $bucket91to180 = ['count' => 0, 'amount' => 0]; // 91-180 days
        $bucket180plus = ['count' => 0, 'amount' => 0]; // 180+ days

        foreach ($loans as $loan) {
            // Calculate loan metrics
            $totalDue = $loan->schedule->sum(function($schedule) {
                return $schedule->principal + $schedule->interest + ($schedule->fee_amount ?? 0);
            });
            $totalPaid = $loan->schedule->sum(function($schedule) {
                return $schedule->repayments->sum('amount');
            });
            $outstandingAmount = $totalDue - $totalPaid;
            $totalOutstanding += $outstandingAmount;

            // Get days in arrears
            $daysInArrears = $loan->days_in_arrears ?? 0;
            $isDelinquent = $daysInArrears >= $delinquencyDays;

            if ($isDelinquent) {
                $delinquentLoans++;
                $totalDelinquentAmount += $outstandingAmount;

                // Categorize into buckets
                if ($daysInArrears >= 1 && $daysInArrears <= 30) {
                    $bucket1to30['count']++;
                    $bucket1to30['amount'] += $outstandingAmount;
                } elseif ($daysInArrears >= 31 && $daysInArrears <= 60) {
                    $bucket31to60['count']++;
                    $bucket31to60['amount'] += $outstandingAmount;
                } elseif ($daysInArrears >= 61 && $daysInArrears <= 90) {
                    $bucket61to90['count']++;
                    $bucket61to90['amount'] += $outstandingAmount;
                } elseif ($daysInArrears >= 91 && $daysInArrears <= 180) {
                    $bucket91to180['count']++;
                    $bucket91to180['amount'] += $outstandingAmount;
                } else {
                    $bucket180plus['count']++;
                    $bucket180plus['amount'] += $outstandingAmount;
                }

                $delinquencyData[] = [
                    'loan_id' => $loan->id,
                    'customer' => $loan->customer->name ?? 'N/A',
                    'customer_no' => $loan->customer->customerNo ?? 'N/A',
                    'phone' => $loan->customer->phone1 ?? 'N/A',
                    'branch' => $loan->branch->name ?? 'N/A',
                    'group' => $loan->group->name ?? 'N/A',
                    'loan_officer' => $loan->loanOfficer->name ?? 'N/A',
                    'outstanding_amount' => $outstandingAmount,
                    'days_in_arrears' => $daysInArrears,
                    'delinquency_bucket' => $this->getDelinquencyBucket($daysInArrears),
                    'severity_level' => $this->getSeverityLevel($daysInArrears),
                    'disbursed_date' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('Y-m-d') : 'N/A', // Use 'disbursed_on'
                    'last_payment_date' => $this->getLastPaymentDate($loan),
                    'next_due_date' => $this->getNextDueDate($loan),
                ];
            }
        }

        // Apply bucket filter if specified
        if ($bucket && !empty($delinquencyData)) {
            $delinquencyData = collect($delinquencyData)->filter(function($loan) use ($bucket) {
                $daysInArrears = $loan['days_in_arrears'];

                switch ($bucket) {
                    case '1-30':
                        return $daysInArrears >= 1 && $daysInArrears <= 30;
                    case '31-60':
                        return $daysInArrears >= 31 && $daysInArrears <= 60;
                    case '61-90':
                        return $daysInArrears >= 61 && $daysInArrears <= 90;
                    case '91-180':
                        return $daysInArrears >= 91 && $daysInArrears <= 180;
                    case '180+':
                        return $daysInArrears > 180;
                    default:
                        return true;
                }
            })->values()->toArray();

            // Recalculate summary metrics for filtered data
            $delinquentLoans = count($delinquencyData);
            $totalDelinquentAmount = collect($delinquencyData)->sum('outstanding_amount');
        }

        // Calculate percentages
        $delinquencyRate = $totalLoans > 0 ? ($delinquentLoans / $totalLoans) * 100 : 0;
        $delinquentAmountRate = $totalOutstanding > 0 ? ($totalDelinquentAmount / $totalOutstanding) * 100 : 0;

        return [
            'summary' => [
                'total_loans' => $totalLoans,
                'delinquent_loans' => $delinquentLoans,
                'total_delinquent_loans' => $delinquentLoans,
                'average_days_overdue' => $delinquentLoans > 0 ? collect($delinquencyData)->avg('days_in_arrears') : 0,
                'current_loans' => $totalLoans - $delinquentLoans,
                'delinquency_rate' => $delinquencyRate,
                'total_outstanding' => $totalOutstanding,
                'total_delinquent_amount' => $totalDelinquentAmount,
                'delinquent_amount_rate' => $delinquentAmountRate,
                'delinquency_days_threshold' => $delinquencyDays,
            ],
            'buckets' => [
                '1-30' => $bucket1to30,
                '31-60' => $bucket31to60,
                '61-90' => $bucket61to90,
                '91-180' => $bucket91to180,
                '180+' => $bucket180plus,
            ],
            'loans' => $delinquencyData,
        ];
    }

    /**
     * Get Delinquency Bucket
     */
    private function getDelinquencyBucket($daysInArrears)
    {
        if ($daysInArrears >= 1 && $daysInArrears <= 30) {
            return '1-30 Days';
        } elseif ($daysInArrears >= 31 && $daysInArrears <= 60) {
            return '31-60 Days';
        } elseif ($daysInArrears >= 61 && $daysInArrears <= 90) {
            return '61-90 Days';
        } elseif ($daysInArrears >= 91 && $daysInArrears <= 180) {
            return '91-180 Days';
        } else {
            return '180+ Days';
        }
    }

    /**
     * Get Severity Level
     */
    private function getSeverityLevel($daysInArrears)
    {
        if ($daysInArrears >= 1 && $daysInArrears <= 15) {
            return 'Low';
        } elseif ($daysInArrears >= 16 && $daysInArrears <= 30) {
            return 'Medium';
        } elseif ($daysInArrears >= 31 && $daysInArrears <= 90) {
            return 'High';
        } else {
            return 'Critical';
        }
    }

    /**
     * Get Last Payment Date
     */
    private function getLastPaymentDate($loan)
    {
        $lastRepayment = $loan->schedule()
            ->whereHas('repayments')
            ->with('repayments')
            ->get()
            ->flatMap->repayments
            ->sortByDesc('payment_date')
            ->first();

        return $lastRepayment ? Carbon::parse($lastRepayment->payment_date)->format('Y-m-d') : 'N/A';
    }

    /**
     * Get Next Due Date
     */
    private function getNextDueDate($loan)
    {
        $nextSchedule = $loan->schedule()
            ->where('due_date', '>=', now())
            ->orderBy('due_date')
            ->first();

        return $nextSchedule ? Carbon::parse($nextSchedule->due_date)->format('Y-m-d') : 'N/A';
    }

        /**
     * Non Performing Loan Report - List NPLs with metrics and export options
     */
    public function nonPerformingLoanReport(Request $request)
    {
        $user = auth()->user();
        $company = $user->company;

        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $groupId = $request->get('group_id');
        $loanOfficerId = $request->get('loan_officer_id');
        $exportType = $request->get('export_type');

        // Get user's assigned branches
        $branches = $user->branches()
            ->where('branches.company_id', $company->id)
            ->select('branches.id', 'branches.name')
            ->get();

        // If user has exactly one branch, force-select it
        if (($branches->count() ?? 0) === 1) {
            $branchId = $branches->first()->id;
        }

        $loanOfficers = User::whereHas('roles', function ($q) {
            $q->where('name', 'like', '%officer%');
        })
            ->when($branchId, function ($query) use ($branchId) {
                if ($branchId !== 'all') {
                    $query->whereHas('branches', function ($q) use ($branchId) {
                        $q->where('branches.id', $branchId);
                    });
                }
            })
            ->get();
        $company = Company::first();
        $groups = Group::all();

        $showData = $request->has('as_of_date') || $request->has('branch_id') || $request->has('loan_officer_id') || $request->isMethod('get');
        $nplData = null;
        $nplSummary = [
            'total_npl_loans' => 0,
            'total_npl_amount' => 0,
            'average_dpd' => 0,
            'provision_total' => 0,
        ];
        if ($showData) {
            $nplData = $this->getNPLData($asOfDate, $branchId, $loanOfficerId, $groupId);
            if (count($nplData) > 0) {
                $nplSummary['total_npl_loans'] = count($nplData);
                $nplSummary['total_npl_amount'] = collect($nplData)->sum('outstanding');
                $nplSummary['average_dpd'] = round(collect($nplData)->avg('dpd'), 1);
                $nplSummary['provision_total'] = collect($nplData)->sum('provision_amount');
            }
            if ($exportType === 'excel') {
                return $this->exportNPLToExcel($request);
            } elseif ($exportType === 'pdf') {
                return $this->exportNPLToPdf($request);
            }
        }
        return view('loans.reports.npl_report', compact('nplData', 'nplSummary', 'branches', 'loanOfficers', 'company', 'asOfDate', 'branchId', 'loanOfficerId', 'showData','groups') );
    }

    /**
     * Query NPL data from database
     */
    private function getNPLData($asOfDate, $branchId = null, $loanOfficerId = null, $groupId = null)
    {
        $user = auth()->user();
        $company = $user->company;

        // Get user's assigned branch IDs for filtering
        $assignedBranchIds = $user->branches()
            ->where('branches.company_id', $company->id)
            ->pluck('branches.id')
            ->toArray();

        $query = Loan::with(['customer', 'branch','group','loanOfficer', 'collaterals', 'schedule.repayments'])
            ->where('status', 'active')
            ->whereDate('disbursed_on', '<=', $asOfDate)
            ->whereIn('branch_id', $assignedBranchIds);
        if ($branchId && $branchId !== 'all') {
            $query->where('branch_id', $branchId);
        }
        if ($loanOfficerId) {
            $query->where('loan_officer_id', $loanOfficerId);
        }
        if ($groupId) {
            $query->where('group_id', $groupId);
        }
        $loans = $query->get();
        $nplData = [];

        foreach ($loans as $loan) {
            $maxDpd = 0;
            $hasNplSchedule = false;
            $totalOutstanding = 0;
            $nplOutstanding = 0;

            foreach ($loan->schedule as $schedule) {
                // Calculate outstanding amount for this schedule
                $totalDue = $schedule->principal + $schedule->interest + ($schedule->fee_amount ?? 0);
                $totalPaid = $schedule->repayments->sum(function($repayment) {
                    return $repayment->principal + $repayment->interest + ($repayment->fee_amount ?? 0);
                });
                $outstanding = $totalDue - $totalPaid;
                $totalOutstanding += $outstanding;

                // Check if this schedule is overdue and has outstanding amount
                if ($schedule->due_date < $asOfDate && $outstanding > 0) {
                    $dpd = Carbon::parse($asOfDate)->diffInDays(Carbon::parse($schedule->due_date), false);
                    // Use absolute value since diffInDays returns negative for past dates
                    $dpd = abs($dpd);
                    if ($dpd > $maxDpd) {
                        $maxDpd = $dpd;
                    }
                    if ($dpd > 90) {
                        $hasNplSchedule = true;
                        $nplOutstanding += $outstanding;
                    }
                }
            }

            // Only include loans that have NPL schedules (overdue > 90 days with outstanding amounts)
            if ($hasNplSchedule && $nplOutstanding > 0) {
                $nplData[] = [
                    'date_of' => $asOfDate,
                    'branch' => $loan->branch->name ?? '',
                    'loan_officer' => $loan->loanOfficer->name ?? '',
                    'loan_id' => $loan->loanNo ?? $loan->id,
                    'borrower' => $loan->customer->name ?? '',
                    'outstanding' => $totalOutstanding, // Total outstanding for the loan
                    'npl_outstanding' => $nplOutstanding, // Only NPL portion
                    'dpd' => $maxDpd,
                    'classification' => $maxDpd > 360 ? 'Loss' : ($maxDpd > 180 ? 'Doubtful' : ($maxDpd > 90 ? 'Substandard' : 'Standard')),
                    'provision_percent' => $maxDpd > 360 ? '100%' : ($maxDpd > 180 ? '50%' : ($maxDpd > 90 ? '20%' : '0%')),
                    'provision_amount' => $nplOutstanding * ($maxDpd > 360 ? 1 : ($maxDpd > 180 ? 0.5 : ($maxDpd > 90 ? 0.2 : 0))),
                    'collateral' => $loan->collaterals->pluck('type')->implode(', '),
                    'status' => $loan->status ?? '',
                    'disbursed_date' => $loan->disbursed_on ? Carbon::parse($loan->disbursed_on)->format('d-m-Y') : 'N/A',
                    'last_payment_date' => $this->getLastPaymentDate($loan),
                ];
            }
        }
        return $nplData;
    }

    /**
     * Export NPL Report to Excel
     */
    public function exportNPLToExcel(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $groupId = $request->get('group_id');
        $loanOfficerId = $request->get('loan_officer_id');
        $nplData = $this->getNPLData($asOfDate, $branchId, $loanOfficerId);
        $filename = 'npl_report_' . $asOfDate . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\NPLExport($nplData, $asOfDate, $branchId, $loanOfficerId), $filename);
    }

    /**
     * Export NPL Report to PDF
     */
    public function exportNPLToPdf(Request $request)
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $branchId = $request->get('branch_id');
        $loanOfficerId = $request->get('loan_officer_id');
        $groupId = $request->get('group_id');
        $nplData = $this->getNPLData($asOfDate, $branchId, $loanOfficerId, $groupId);
        $company = Company::first();
        $pdf = \PDF::loadView('loans.reports.npl_report_pdf', compact('nplData', 'asOfDate', 'branchId', 'loanOfficerId', 'company'));
        $pdf->setPaper('A3', 'landscape');
        $filename = 'npl_report_' . $asOfDate . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Export Loan Outstanding Balance Report to Excel
     */
    private function exportLoanOutstandingToExcel($outstandingData, $summary, $asOfDate, $branchId = null, $loanOfficerId = null)
    {
        $branch = $branchId ? Branch::find($branchId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;

        return \Maatwebsite\Excel\Facades\Excel::download(new class($outstandingData, $summary, $asOfDate, $branch, $loanOfficer) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle, \Maatwebsite\Excel\Concerns\WithStyles, \Maatwebsite\Excel\Concerns\ShouldAutoSize {
            private $outstandingData;
            private $summary;
            private $asOfDate;
            private $branch;
            private $loanOfficer;

            public function __construct($outstandingData, $summary, $asOfDate, $branch, $loanOfficer)
            {
                $this->outstandingData = collect($outstandingData);
                $this->summary = $summary;
                $this->asOfDate = $asOfDate;
                $this->branch = $branch;
                $this->loanOfficer = $loanOfficer;
            }

            public function collection()
            {
                return $this->outstandingData->map(function ($row) {
                    return [
                        'Customer' => $row['customer'],
                        'Customer No' => $row['customer_no'],
                        'Phone' => $row['phone'],
                        'Loan No' => $row['loan_no'],
                        'Disbursed Amount' => $row['amount'],
                        'Expected Interest' => $row['interest'],
                        'Disbursed Date' => $row['disbursed_no'],
                        'Expiry' => $row['expiry'],
                        'Branch' => $row['branch'],
                        'Loan Officer' => $row['loan_officer'],
                        'Principal Paid' => $row['principal_paid'],
                        'Interest Paid' => $row['interest_paid'],
                        'Outstanding Principal' => $row['amount'] - $row['principal_paid'],
                        'Outstanding Interest' => $row['outstanding_interest'],
                        'Accrued Interest' => $row['accrued_interest'],
                        'Not Due Interest' => $row['not_due_interest'],
                        'Outstanding Balance' => $row['outstanding_balance'],
                    ];
                });
            }

            public function headings(): array
            {
                return [
                    'Customer',
                    'Customer No',
                    'Phone',
                    'Loan No',
                    'Disbursed Amount',
                    'Expected Interest',
                    'Disbursed Date',
                    'Expiry',
                    'Branch',
                    'Loan Officer',
                    'Principal Paid',
                    'Interest Paid',
                    'Outstanding Principal',
                    'Outstanding Interest',
                    'Accrued Interest',
                    'Not Due Interest',
                    'Outstanding Balance',
                ];
            }

            public function title(): string
            {
                return 'Loan Outstanding Balance Report';
            }

            public function styles(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet)
            {
                return [
                    1 => ['font' => ['bold' => true]],
                ];
            }
        }, 'loan_outstanding_balance_' . $asOfDate . '.xlsx');
    }

    /**
     * Export Loan Outstanding Balance Report to PDF
     */
    private function exportLoanOutstandingToPdf($outstandingData, $summary, $asOfDate, $branchId = null, $loanOfficerId = null)
    {
        $branch = $branchId ? Branch::find($branchId) : null;
        $loanOfficer = $loanOfficerId ? User::find($loanOfficerId) : null;
        $company = Company::first();

        $pdf = \PDF::loadView('loans.reports.loan_outstanding_pdf', compact('outstandingData', 'summary', 'asOfDate', 'branch', 'loanOfficer', 'company'));
        $pdf->setPaper('A3', 'landscape');
        $filename = 'loan_outstanding_balance_' . $asOfDate . '.pdf';
        return $pdf->download($filename);
    }
}
