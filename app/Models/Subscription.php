<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'plan_name',
        'plan_description',
        'amount',
        'currency',
        'billing_cycle',
        'start_date',
        'end_date',
        'status',
        'payment_status',
        'payment_method',
        'transaction_id',
        'payment_notes',
        'payment_date',
        'last_reminder_sent',
        'reminder_count',
        'auto_renew',
        'features',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'payment_date' => 'datetime',
        'last_reminder_sent' => 'datetime',
        'auto_renew' => 'boolean',
        'features' => 'array',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the company that owns the subscription.
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Scope to get active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get paid subscriptions
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    /**
     * Scope to get subscriptions expiring soon
     */
    public function scopeExpiringSoon($query, $days = 5)
    {
        return $query->where('end_date', '<=', Carbon::now()->addDays($days))
            ->where('status', 'active');
    }

    /**
     * Scope to get expired subscriptions
     */
    public function scopeExpired($query)
    {
        return $query->where('end_date', '<', Carbon::now())
            ->where('status', 'active');
    }

    /**
     * Check if subscription is active
     */
    public function isActive()
    {
        return $this->status === 'active' &&
            $this->payment_status === 'paid' &&
            $this->end_date >= Carbon::now();
    }

    /**
     * Check if subscription is expired
     */
    public function isExpired()
    {
        return $this->end_date < Carbon::now();
    }

    /**
     * Check if subscription is expiring soon
     */
    public function isExpiringSoon($days = 5)
    {
        return $this->end_date <= Carbon::now()->addDays($days) &&
            $this->end_date > Carbon::now();
    }

    /**
     * Get days until expiry
     */
    public function daysUntilExpiry()
    {
        return Carbon::now()->diffInDays($this->end_date, false);
    }

    /**
     * Mark subscription as paid
     */
    public function markAsPaid($paymentMethod = null, $transactionId = null, $notes = null)
    {
        $this->update([
            'payment_status' => 'paid',
            'payment_method' => $paymentMethod,
            'transaction_id' => $transactionId,
            'payment_notes' => $notes,
            'payment_date' => Carbon::now(),
            'status' => 'active',
        ]);
    }

    /**
     * Mark subscription as expired
     */
    public function markAsExpired()
    {
        $this->update([
            'status' => 'expired',
        ]);
    }

    /**
     * Extend subscription
     */
    public function extend($days)
    {
        $newEndDate = $this->end_date->copy()->addDays($days);

        $updateData = ['end_date' => $newEndDate];

        // Auto-update status based on new end date and payment status
        if ($this->payment_status === 'paid') {
            if ($newEndDate->isFuture()) {
                // Subscription is paid and not expired - make it active
                $updateData['status'] = 'active';
            } else {
                // Subscription is paid but still expired - keep as expired
                $updateData['status'] = 'expired';
            }
        }

        $this->update($updateData);
    }

    /**
     * Renew subscription
     */
    public function renew($billingCycle = null)
    {
        $cycle = $billingCycle ?? $this->billing_cycle;
        $days = $this->getBillingCycleDays($cycle);

        $newEndDate = $this->end_date->copy()->addDays($days);

        $this->update([
            'start_date' => $this->end_date,
            'end_date' => $newEndDate,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_date' => null,
            'transaction_id' => null,
            'payment_notes' => null,
        ]);
    }

    /**
     * Get billing cycle days
     */
    private function getBillingCycleDays($cycle)
    {
        switch ($cycle) {
            case 'monthly':
                return 30;
            case 'quarterly':
                return 90;
            case 'half-yearly':
                return 180;
            case 'yearly':
                return 365;
            default:
                return 30;
        }
    }

    /**
     * Get subscription status badge class
     */
    public function getStatusBadgeClass()
    {
        switch ($this->status) {
            case 'active':
                return 'badge-success';
            case 'inactive':
                return 'badge-secondary';
            case 'expired':
                return 'badge-danger';
            case 'cancelled':
                return 'badge-warning';
            case 'pending':
                return 'badge-info';
            default:
                return 'badge-secondary';
        }
    }

    /**
     * Get payment status badge class
     */
    public function getPaymentStatusBadgeClass()
    {
        switch ($this->payment_status) {
            case 'paid':
                return 'badge-success';
            case 'unpaid':
                return 'badge-danger';
            case 'pending':
                return 'badge-warning';
            case 'failed':
                return 'badge-danger';
            default:
                return 'badge-secondary';
        }
    }
}