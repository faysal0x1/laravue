<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'initial_balance',
        'current_balance',
        'description',
        'is_active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'from_account_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class, 'to_account_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper Methods
    public function getBalance()
    {
        return $this->current_balance;
    }

    public function addBalance($amount)
    {
        $this->increment('current_balance', $amount);
    }

    public function deductBalance($amount)
    {
        $this->decrement('current_balance', $amount);
    }

    public function getTypeLabel()
    {
        return ucfirst($this->type);
    }

    public function getTotalIncome($startDate = null, $endDate = null)
    {
        $query = $this->incomes()->where('status', 'completed');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        return $query->sum('amount');
    }

    public function getTotalExpense($startDate = null, $endDate = null)
    {
        $query = $this->expenses()->where('status', 'completed');

        if ($startDate) {
            $query->whereDate('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('transaction_date', '<=', $endDate);
        }

        return $query->sum('amount');
    }

    public function getRecentTransactions($limit = 10)
    {
        $incomes = $this->incomes()
            ->select('id', 'account_id', 'amount', 'transaction_date', 'description', \DB::raw('"income" as type'))
            ->latest('transaction_date');

        $expenses = $this->expenses()
            ->select('id', 'account_id', 'amount', 'transaction_date', 'description', \DB::raw('"expense" as type'))
            ->latest('transaction_date')
            ->union($incomes);

        return $expenses->limit($limit)->get();
    }
}
