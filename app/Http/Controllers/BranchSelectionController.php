<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Branch;

class BranchSelectionController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $branches = $user->branches()->get();
        return view('auth.select-branch', compact('branches'));
    }

    public function select(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
        ]);
        $user->branch_id = $request->branch_id;
        $user->save();
        return redirect()->route('dashboard');
    }
}
