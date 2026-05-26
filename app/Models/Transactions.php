<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Income extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'income_category_id',
        'amount',
        'description',
        'transaction_date',
        'status',
        'reference_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(IncomeCategory::class, 'income_category_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('transaction_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function getStatusLabel()
    {
        $labels = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColor()
    {
        $colors = [
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();

        // Update account balance
        $this->account->addBalance($this->amount);
    }

    public function markAsCancelled()
    {
        if ($this->status === 'completed') {
            $this->account->deductBalance($this->amount);
        }

        $this->status = 'cancelled';
        $this->save();
    }
}

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'expense_category_id',
        'amount',
        'description',
        'transaction_date',
        'status',
        'payment_method',
        'reference_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('transaction_date', now()->month)
            ->whereYear('transaction_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('transaction_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    public function getStatusLabel()
    {
        $labels = [
            'pending' => 'Pending',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColor()
    {
        $colors = [
            'pending' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function markAsCompleted()
    {
        $this->status = 'completed';
        $this->save();

        // Update account balance
        $this->account->deductBalance($this->amount);

        // Update budget limit
        $this->updateBudgetLimit();
    }

    public function markAsCancelled()
    {
        if ($this->status === 'completed') {
            $this->account->addBalance($this->amount);
        }

        $this->status = 'cancelled';
        $this->save();

        // Revert budget update
        $this->updateBudgetLimit(true);
    }

    private function updateBudgetLimit($revert = false)
    {
        $budgetLimit = BudgetLimit::where('user_id', $this->user_id)
            ->where('expense_category_id', $this->expense_category_id)
            ->where('period_start_date', '<=', $this->transaction_date->format('Y-m-d'))
            ->where('period_end_date', '>=', $this->transaction_date->format('Y-m-d'))
            ->first();

        if ($budgetLimit) {
            if ($revert) {
                $budgetLimit->decrement('spent_amount', $this->amount);
            } else {
                $budgetLimit->increment('spent_amount', $this->amount);

                // Check if exceeded
                if ($budgetLimit->spent_amount >= $budgetLimit->limit_amount) {
                    $budgetLimit->status = 'exceeded';
                    $budgetLimit->save();
                }
            }
        }
    }
}
