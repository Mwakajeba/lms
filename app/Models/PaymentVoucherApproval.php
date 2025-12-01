<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentVoucherApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_id',
        'approval_level',
        'approver_id',
        'approver_type',
        'approver_name',
        'status',
        'comments',
        'approved_at',
        'escalated_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'escalated_at' => 'datetime',
    ];

    /**
     * Get the payment that owns the approval.
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Get the approver user.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scope for pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for approved approvals.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope for rejected approvals.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    /**
     * Scope for escalated approvals.
     */
    public function scopeEscalated($query)
    {
        return $query->where('status', 'escalated');
    }

    /**
     * Check if approval is pending.
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if approval is approved.
     */
    public function isApproved()
    {
        return $this->status === 'approved';
    }

    /**
     * Check if approval is rejected.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if approval is escalated.
     */
    public function isEscalated()
    {
        return $this->status === 'escalated';
    }

    /**
     * Approve the payment voucher.
     */
    public function approve($comments = null)
    {
        $this->update([
            'status' => 'approved',
            'comments' => $comments,
            'approved_at' => now(),
        ]);
    }

    /**
     * Reject the payment voucher.
     */
    public function reject($comments = null)
    {
        $this->update([
            'status' => 'rejected',
            'comments' => $comments,
            'approved_at' => now(),
        ]);
    }

    /**
     * Escalate the payment voucher.
     */
    public function escalate($comments = null)
    {
        $this->update([
            'status' => 'escalated',
            'comments' => $comments,
            'escalated_at' => now(),
        ]);
    }
}
