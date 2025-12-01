<?php

namespace App\Http\Controllers\Api;

use App\Models\BankAccount;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BankAccountController extends Controller
{
    public function index(Request $request)
    {
        $accounts = BankAccount::select('id', 'name', 'account_number')->get();
        return response()->json($accounts);
    }
}
