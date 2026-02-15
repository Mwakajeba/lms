<?php

namespace App\Http\Controllers\Api;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $accounts = BankAccount::select('id', 'name', 'account_number')
            ->forBranch($user->branch_id ?? null)
            ->get();
        return response()->json($accounts);
    }
}
