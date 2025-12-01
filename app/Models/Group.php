<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'name',
        'loan_officer',
        'branch_id',
        'minimum_members',
        'maximum_members',
        'group_leader',
        'meeting_day',
        'meeting_time',
    ];

    protected $casts = [
        'meeting_time' => 'datetime:H:i',
        'minimum_members' => 'integer',
        'maximum_members' => 'integer',
        'group_leader' => 'integer',
        'loan_officer' => 'integer',
        'branch_id' => 'integer',
    ];

    public function loans()
    {
        // Get loans through group members using hasManyThrough
        return $this->hasManyThrough(
            Loan::class,
            \App\Models\GroupMember::class,
            'group_id', // Foreign key on group_members table
            'customer_id', // Foreign key on loans table
            'id', // Local key on groups table
            'customer_id' // Local key on group_members table
        );
    }

    /**
     * Get all loans for this group's members
     * This is a helper method that returns a query builder
     */
    public function getGroupLoans()
    {
        $memberIds = $this->members()->pluck('customer_id');
        return Loan::whereIn('customer_id', $memberIds);
    }

    /**
     * Get the loan officer (user) for this group.
     */
    public function loanOfficer()
    {
        return $this->belongsTo(User::class, 'loan_officer');
    }

    /**
     * Get the branch for this group.
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * Get the group leader (customer) for this group.
     */
    public function groupLeader()
    {
        return $this->belongsTo(Customer::class, 'group_leader');
    }

    /**
     * Accessor to get the count of loans for this group.
     * 
     */
    public function getLoansCountAttribute()
    {
        return $this->loans()->count();
    }

    /**
     * Get the members of this group.
     */
    public function members()
    {
        return $this->belongsToMany(Customer::class, 'group_members', 'group_id', 'customer_id');
    }

    /**
     * Check if a specific member has any ongoing (not completed) loans within this group.
     */
    public function memberHasOngoingLoans(int $customerId): bool
    {
        // Fetch loans for this member in this group and check non-completed status
        $loans = Loan::where('customer_id', $customerId)
            ->where('group_id', $this->id)
            ->get(['status']);

        foreach ($loans as $loan) {
            $status = is_string($loan->status) ? strtolower($loan->status) : '';
            if ($status !== Loan::STATUS_COMPLETE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Return the special "Individual" group id used as a fallback on member removal.
     */
    public static function getIndividualGroupId(): int
    {
        // Convention: group id 1 is the built-in "Individual" group
        return 1;
    }

    /**
     * Get the count of members in this group.
     */
    public function getMembersCountAttribute()
    {
        return $this->members()->count();
    }

    /**
     * Get the current member count (alias for members_count).
     */
    public function getCurrentMemberCountAttribute()
    {
        return $this->members_count;
    }

    /**
     * Check if the group has reached its maximum member limit.
     */
    public function hasReachedMaxMembers()
    {
        if (!$this->maximum_members) {
            return false;
        }

        return $this->members_count >= $this->maximum_members;
    }

    /**
     * Check if the group can accept more members.
     */
    public function canAcceptMoreMembers()
    {
        if (!$this->maximum_members) {
            return true; // No limit set, can always accept more
        }

        return $this->members_count < $this->maximum_members;
    }

    /**
     * Check if the group has reached its minimum member requirement.
     */
    public function hasReachedMinMembers()
    {
        if (!$this->minimum_members) {
            return true;
        }

        return $this->members_count >= $this->minimum_members;
    }

    /**
     * Get the next meeting date based on meeting_day and meeting_time.
     */
    public function getNextMeetingDate()
    {
        if (!$this->meeting_day || !$this->meeting_time) {
            return null;
        }

        $today = now();
        $meetingTime = $this->meeting_time;

        switch ($this->meeting_day) {
            case 'every_day':
                $nextMeeting = $today->copy()->setTimeFromTimeString($meetingTime);
                if ($nextMeeting->isPast()) {
                    $nextMeeting->addDay();
                }
                break;
            case 'every_week':
                $nextMeeting = $today->copy()->nextWeekday()->setTimeFromTimeString($meetingTime);
                break;
            case 'every_month':
                $nextMeeting = $today->copy()->addMonth()->setTimeFromTimeString($meetingTime);
                break;
            default:
                $dayOfWeek = strtolower($this->meeting_day);
                $nextMeeting = $today->copy()->next($dayOfWeek)->setTimeFromTimeString($meetingTime);
                break;
        }

        return $nextMeeting;
    }

    /**
     * Scope to filter groups by branch.
     */
    public function scopeByBranch($query, $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    /**
     * Scope to filter groups by loan officer.
     */
    public function scopeByLoanOfficer($query, $loanOfficerId)
    {
        return $query->where('loan_officer', $loanOfficerId);
    }

    /**
     * Scope to filter groups that have reached minimum members.
     */
    public function scopeWithMinMembers($query)
    {
        return $query->whereHas('members', function ($q) {
            $q->havingRaw('COUNT(*) >= groups.minimum_members');
        });
    }

    /**
     * Scope to filter groups that haven't reached maximum members.
     */
    public function scopeNotMaxMembers($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('maximum_members')
                ->orWhereHas('members', function ($subQ) {
                    $subQ->havingRaw('COUNT(*) < groups.maximum_members');
                });
        });
    }
}
