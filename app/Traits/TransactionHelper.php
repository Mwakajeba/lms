<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait TransactionHelper
{
    /**
     * Run a database transaction with error handling
     *
     * @param callable $callback
     * @return mixed
     * @throws \Exception
     */
    protected function runTransaction(callable $callback)
    {
        return DB::transaction($callback);
    }
}