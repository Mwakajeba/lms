<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankReconciliation extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'bank_account_id',
        'user_id',
        'branch_id',
        'reconciliation_date',
        'start_date',
        'end_date',
        'bank_statement_balance',
        'book_balance',
        'adjusted_bank_balance',
        'adjusted_book_balance',
        'difference',
        'status',
        'notes',
        'bank_statement_document',
    ];

    // Use Hashids for route model binding like other models
    public function getRouteKey()
    {
        return \App\Helpers\HashIdHelper::encode($this->id);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        $id = \App\Helpers\HashIdHelper::decode($value);
        if (!$id) {
            return null;
        }
        return $this->where($field ?: $this->getKeyName(), $id)->first();
    }

    protected $casts = [
        'reconciliation_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
        'bank_statement_balance' => 'decimal:2',
        'book_balance' => 'decimal:2',
        'adjusted_bank_balance' => 'decimal:2',
        'adjusted_book_balance' => 'decimal:2',
        'difference' => 'decimal:2',
    ];

    // Relationships
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function reconciliationItems(): HasMany
    {
        return $this->hasMany(BankReconciliationItem::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByBankAccount($query, $bankAccountId)
    {
        return $query->where('bank_account_id', $bankAccountId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('reconciliation_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedReconciliationDateAttribute()
    {
        return $this->reconciliation_date ? $this->reconciliation_date->format('M d, Y') : 'N/A';
    }

    public function getFormattedStartDateAttribute()
    {
        return $this->start_date ? $this->start_date->format('M d, Y') : 'N/A';
    }

    public function getFormattedEndDateAttribute()
    {
        return $this->end_date ? $this->end_date->format('M d, Y') : 'N/A';
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'draft' => '<span class="badge bg-secondary">Draft</span>',
            'in_progress' => '<span class="badge bg-warning">In Progress</span>',
            'completed' => '<span class="badge bg-success">Completed</span>',
            'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        ];

        return $badges[$this->status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getFormattedBankStatementBalanceAttribute()
    {
        return number_format($this->bank_statement_balance, 2);
    }

    public function getFormattedBookBalanceAttribute()
    {
        return number_format($this->book_balance, 2);
    }

    public function getFormattedDifferenceAttribute()
    {
        return number_format($this->difference, 2);
    }

    public function getFormattedAdjustedBankBalanceAttribute()
    {
        return number_format($this->adjusted_bank_balance, 2);
    }

    public function getFormattedAdjustedBookBalanceAttribute()
    {
        return number_format($this->adjusted_book_balance, 2);
    }

    // Methods
    public function calculateBookBalance()
    {
        // Get GL transactions for this bank account within the date range
        $glTransactions = GlTransaction::where('chart_account_id', $this->bankAccount->chart_account_id)
            ->whereBetween('date', [$this->start_date, $this->end_date])
            ->get();

        $debits = $glTransactions->where('nature', 'debit')->sum('amount');
        $credits = $glTransactions->where('nature', 'credit')->sum('amount');

        // For bank accounts, debits increase balance, credits decrease balance
        $this->book_balance = $debits - $credits;
        $this->save();

        return $this->book_balance;
    }

    public function calculateDifference()
    {
        $this->difference = $this->adjusted_bank_balance - $this->adjusted_book_balance;
        $this->save();

        return $this->difference;
    }

    public function isBalanced()
    {
        return abs($this->difference) < 0.01; // Allow for small rounding differences
    }

    public function getUnreconciledItems()
    {
        return $this->reconciliationItems()->where('is_reconciled', false)->get();
    }

    public function getReconciledItems()
    {
        return $this->reconciliationItems()->where('is_reconciled', true)->get();
    }

    /**
     * Get summary of unreconciled items
     */
    public function getUnreconciledSummary()
    {
        $unreconciledBankItems = $this->reconciliationItems()
            ->where('is_bank_statement_item', true)
            ->where('is_reconciled', false)
            ->get();

        $unreconciledBookItems = $this->reconciliationItems()
            ->where('is_book_entry', true)
            ->where('is_reconciled', false)
            ->get();

        return [
            'bank_items_count' => $unreconciledBankItems->count(),
            'bank_items_total' => $unreconciledBankItems->sum('amount'),
            'book_items_count' => $unreconciledBookItems->count(),
            'book_items_total' => $unreconciledBookItems->sum('amount'),
            'total_unreconciled' => $unreconciledBankItems->count() + $unreconciledBookItems->count(),
        ];
    }

    /**
     * Recalculate adjusted balances based on reconciliation items
     */
    public function recalculateAdjustedBalances()
    {
        // Calculate adjusted bank balance
        $bankItems = $this->reconciliationItems()
            ->where('is_bank_statement_item', true)
            ->get();

        $bankDebits = $bankItems->where('nature', 'debit')->sum('amount');
        $bankCredits = $bankItems->where('nature', 'credit')->sum('amount');
        $adjustedBankBalance = $this->bank_statement_balance + $bankDebits - $bankCredits;

        // Calculate adjusted book balance
        $bookItems = $this->reconciliationItems()
            ->where('is_book_entry', true)
            ->where('is_reconciled', false)
            ->get();

        $bookDebits = $bookItems->where('nature', 'debit')->sum('amount');
        $bookCredits = $bookItems->where('nature', 'credit')->sum('amount');
        $adjustedBookBalance = $this->book_balance + $bookDebits - $bookCredits;

        $this->update([
            'adjusted_bank_balance' => $adjustedBankBalance,
            'adjusted_book_balance' => $adjustedBookBalance,
        ]);

        $this->calculateDifference();
    }
}
