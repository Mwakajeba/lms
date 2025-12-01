<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        return view('reports.index', compact('user'));
    }

    public function loans()
    {
        $user = Auth::user();
        
        return view('reports.loans', compact('user'));
    }

    public function customers()
    {
        $user = Auth::user();
        
        return view('reports.customers', compact('user'));
    }

    public function bot()
    {
        $user = Auth::user();
        
        return view('reports.bot', compact('user'));
    }
} 