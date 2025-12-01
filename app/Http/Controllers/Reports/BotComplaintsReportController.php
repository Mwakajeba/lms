<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BotComplaintsReportController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));

        $rows = [
            'Number of complaints at the beginning of the quarter',
            'New complaints received during the quarter',
            'Complaints resolved during the quarter by the institution',
            'Complaints resolved during the quarter by other parties (eg Courts, Bank of Tanzania, FCC)',
            'Unresolved complaints at the end of the quarter(1+2-3-4)',
            'Unresolved complaints referred to Bank of Tanzania',
            'Unresolved complaints referred to Fair Competition Commission',
            'Unresolved complaints referred to Courts',
            'Unresolved complaints referred to Other Parties',
        ];

        return view('reports.bot.complaints', compact('user', 'asOfDate', 'rows'));
    }

    public function export(Request $request): StreamedResponse
    {
        $asOfDate = $request->get('as_of_date', now()->format('Y-m-d'));
        $filename = 'BOT_Complaints_' . $asOfDate . '.xls';
        $fullPath = base_path('resources/views/reports/bot-complaints.xls');
        if (!file_exists($fullPath)) {
            return response()->streamDownload(function () {
                echo 'Template not found';
            }, $filename);
        }
        return response()->streamDownload(function () use ($fullPath) {
            readfile($fullPath);
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel'
        ]);
    }
} 