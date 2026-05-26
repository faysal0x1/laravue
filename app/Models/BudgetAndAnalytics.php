<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class BudgetLimit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'expense_category_id',
        'period',
        'limit_amount',
        'spent_amount',
        'period_start_date',
        'period_end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'limit_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'period_start_date' => 'date',
        'period_end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExceeded($query)
    {
        return $query->where('status', 'exceeded');
    }

    public function scopeByPeriod($query, $period)
    {
        return $query->where('period', $period);
    }

    public function scopeForCategory($query, $categoryId)
    {
        return $query->where('expense_category_id', $categoryId);
    }

    public function getRemaining()
    {
        return $this->limit_amount - $this->spent_amount;
    }

    public function getPercentageUsed()
    {
        if ($this->limit_amount == 0) {
            return 0;
        }

        return round(($this->spent_amount / $this->limit_amount) * 100, 2);
    }

    public function isExceeded()
    {
        return $this->spent_amount >= $this->limit_amount;
    }

    public function getStatusLabel()
    {
        $labels = [
            'active' => 'Active',
            'inactive' => 'Inactive',
            'exceeded' => 'Exceeded',
        ];

        return $labels[$this->status] ?? $this->status;
    }

    public function getStatusColor()
    {
        $colors = [
            'active' => 'success',
            'inactive' => 'secondary',
            'exceeded' => 'danger',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    public function getPeriodLabel()
    {
        $labels = [
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'yearly' => 'Yearly',
        ];

        return $labels[$this->period] ?? $this->period;
    }

    public function isExpiringSoon()
    {
        return $this->period_end_date->diffInDays(now()) <= 3;
    }

    public function getDaysRemaining()
    {
        return $this->period_end_date->diffInDays(now());
    }

    public function resetSpent()
    {
        $this->spent_amount = 0;
        $this->status = 'active';
        $this->save();
    }
}

class AnalyticsCache extends Model
{
    protected $fillable = [
        'user_id',
        'period_type',
        'period_date',
        'total_income',
        'total_expense',
        'net_income',
        'category_breakdown',
        'account_breakdown',
    ];

    protected $casts = [
        'total_income' => 'decimal:2',
        'total_expense' => 'decimal:2',
        'net_income' => 'decimal:2',
        'category_breakdown' => 'array',
        'account_breakdown' => 'array',
        'period_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const UPDATED_AT = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByPeriodType($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    public function scopeForMonth($query, $month, $year)
    {
        return $query->where('period_type', 'monthly')
            ->whereMonth('period_date', $month)
            ->whereYear('period_date', $year);
    }

    public function scopeForWeek($query, $date)
    {
        $startOfWeek = $date->copy()->startOfWeek();

        return $query->where('period_type', 'weekly')
            ->whereDate('period_date', $startOfWeek);
    }

    public function scopeForYear($query, $year)
    {
        return $query->where('period_type', 'yearly')
            ->whereYear('period_date', $year);
    }

    public function scopeLatest($query, $column = 'period_date')
    {
        return $query->orderBy($column, 'desc');
    }

    public function getCategoryBreakdownArray()
    {
        return $this->category_breakdown ?? [];
    }

    public function getAccountBreakdownArray()
    {
        return $this->account_breakdown ?? [];
    }

    public function getSavingsRate()
    {
        if ($this->total_income == 0) {
            return 0;
        }

        return round(($this->net_income / $this->total_income) * 100, 2);
    }

    public function getExpensePercentage()
    {
        if ($this->total_income == 0) {
            return 0;
        }

        return round(($this->total_expense / $this->total_income) * 100, 2);
    }

    public static function refreshCache(User $user)
    {
        // This would be called by a scheduled job or after transactions
        // to update the cache with latest data
    }
}
