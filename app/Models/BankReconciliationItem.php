<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankReconciliationItem extends Model
{
    use HasFactory,LogsActivity;

    protected $fillable = [
        'bank_reconciliation_id',
        'gl_transaction_id',
        'transaction_type',
        'reference',
        'description',
        'transaction_date',
        'amount',
        'nature',
        'is_reconciled',
        'is_bank_statement_item',
        'is_book_entry',
        'matched_with_item_id',
        'reconciled_at',
        'reconciled_by',
        'notes',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'amount' => 'decimal:2',
        'is_reconciled' => 'boolean',
        'is_bank_statement_item' => 'boolean',
        'is_book_entry' => 'boolean',
        'reconciled_at' => 'datetime',
    ];

    // Relationships
    public function bankReconciliation(): BelongsTo
    {
        return $this->belongsTo(BankReconciliation::class);
    }

    public function glTransaction(): BelongsTo
    {
        return $this->belongsTo(GlTransaction::class);
    }

    public function matchedWithItem(): BelongsTo
    {
        return $this->belongsTo(BankReconciliationItem::class, 'matched_with_item_id');
    }

    public function reconciledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reconciled_by');
    }

    // Scopes
    public function scopeReconciled($query)
    {
        return $query->where('is_reconciled', true);
    }

    public function scopeUnreconciled($query)
    {
        return $query->where('is_reconciled', false);
    }

    public function scopeBankStatementItems($query)
    {
        return $query->where('is_bank_statement_item', true);
    }

    public function scopeBookEntries($query)
    {
        return $query->where('is_book_entry', true);
    }

    public function scopeByTransactionType($query, $type)
    {
        return $query->where('transaction_type', $type);
    }

    // Accessors
    public function getFormattedTransactionDateAttribute()
    {
        return $this->transaction_date ? $this->transaction_date->format('M d, Y') : 'N/A';
    }

    public function getFormattedAmountAttribute()
    {
        return number_format($this->amount, 2);
    }

    public function getNatureBadgeAttribute()
    {
        $badges = [
            'debit' => '<span class="badge bg-danger">Debit</span>',
            'credit' => '<span class="badge bg-success">Credit</span>',
        ];

        return $badges[$this->nature] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    public function getReconciliationStatusBadgeAttribute()
    {
        if ($this->is_reconciled) {
            return '<span class="badge bg-success">Reconciled</span>';
        }
        return '<span class="badge bg-warning">Unreconciled</span>';
    }

    public function getTransactionTypeBadgeAttribute()
    {
        $badges = [
            'bank_statement' => '<span class="badge bg-info">Bank Statement</span>',
            'book_entry' => '<span class="badge bg-primary">Book Entry</span>',
            'adjustment' => '<span class="badge bg-warning">Adjustment</span>',
        ];

        return $badges[$this->transaction_type] ?? '<span class="badge bg-secondary">Unknown</span>';
    }

    // Methods
    public function markAsReconciled($userId = null)
    {
        $this->update([
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => $userId ?? auth()->id(),
        ]);
    }

    public function markAsUnreconciled()
    {
        $this->update([
            'is_reconciled' => false,
            'matched_with_item_id' => null,
            'reconciled_at' => null,
            'reconciled_by' => null,
        ]);
    }

    public function matchWith($itemId)
    {
        $this->update([
            'matched_with_item_id' => $itemId,
            'is_reconciled' => true,
            'reconciled_at' => now(),
            'reconciled_by' => auth()->id(),
        ]);
    }

    public function getMatchedItem()
    {
        return $this->matchedWithItem;
    }

    public function isMatched()
    {
        return !is_null($this->matched_with_item_id);
    }
}
