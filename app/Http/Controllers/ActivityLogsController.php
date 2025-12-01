<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogsController extends Controller
{

    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')->get();

        return view('logs.index', compact('logs'));
    }

    public function show($id)
    {
        $log = ActivityLog::with('user')->findOrFail($id);
        return view('activity_logs.show', compact('log'));
    }
}
