<?php

namespace App\Http\Controllers\Accounting\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class OtherIncomeReportController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('view other income report')) {
            abort(403, 'Unauthorized access to this report.');
        }
        
        // TODO: Implement other income report logic
        return view('accounting.reports.other-income.index');
    }
}
