<?php

namespace App\Notifications;

use App\Models\BankReconciliation;
use App\Models\GlTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BankReconciliationUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    protected $bankReconciliation;
    protected $glTransaction;
    protected $oldBookBalance;
    protected $newBookBalance;

    /**
     * Create a new notification instance.
     */
    public function __construct(BankReconciliation $bankReconciliation, GlTransaction $glTransaction, $oldBookBalance, $newBookBalance)
    {
        $this->bankReconciliation = $bankReconciliation;
        $this->glTransaction = $glTransaction;
        $this->oldBookBalance = $oldBookBalance;
        $this->newBookBalance = $newBookBalance;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $difference = $this->newBookBalance - $this->oldBookBalance;
        $differenceText = $difference >= 0 ? "+" . number_format($difference, 2) : number_format($difference, 2);

        return (new MailMessage)
            ->subject('Bank Reconciliation Automatically Updated')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your bank reconciliation has been automatically updated due to a new transaction.')
            ->line('**Bank Account:** ' . $this->bankReconciliation->bankAccount->name)
            ->line('**Reconciliation Period:** ' . $this->bankReconciliation->start_date->format('M d, Y') . ' to ' . $this->bankReconciliation->end_date->format('M d, Y'))
            ->line('**New Transaction:** ' . $this->glTransaction->description)
            ->line('**Transaction Amount:** ' . number_format($this->glTransaction->amount, 2) . ' (' . ucfirst($this->glTransaction->nature) . ')')
            ->line('**Book Balance Change:** ' . $differenceText)
            ->line('**New Book Balance:** ' . number_format($this->newBookBalance, 2))
            ->action('View Reconciliation', route('accounting.bank-reconciliation.show', $this->bankReconciliation))
            ->line('The reconciliation will continue to update automatically as new transactions are added.')
            ->line('Thank you for using our system!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $difference = $this->newBookBalance - $this->oldBookBalance;
        $differenceText = $difference >= 0 ? "+" . number_format($difference, 2) : number_format($difference, 2);

        return [
            'type' => 'bank_reconciliation_updated',
            'bank_reconciliation_id' => $this->bankReconciliation->id,
            'bank_account_name' => $this->bankReconciliation->bankAccount->name,
            'gl_transaction_id' => $this->glTransaction->id,
            'transaction_description' => $this->glTransaction->description,
            'transaction_amount' => $this->glTransaction->amount,
            'transaction_nature' => $this->glTransaction->nature,
            'old_book_balance' => $this->oldBookBalance,
            'new_book_balance' => $this->newBookBalance,
            'balance_change' => $differenceText,
            'reconciliation_period' => $this->bankReconciliation->start_date->format('M d, Y') . ' to ' . $this->bankReconciliation->end_date->format('M d, Y'),
            'message' => "Bank reconciliation for {$this->bankReconciliation->bankAccount->name} has been automatically updated. Book balance changed by {$differenceText}.",
        ];
    }
} 