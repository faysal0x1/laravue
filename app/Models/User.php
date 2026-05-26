<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Concerns\HasTeams;
use App\Traits\BelongsToShop;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'shop_id',
    'name',
    'email',
    'phone',
    'password',
    'type',
    'avatar',
    'is_active',
    'current_team_id',
])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use BelongsToShop, HasApiTokens,HasFactory, HasRoles, HasTeams, Notifiable, TwoFactorAuthenticatable {
        HasTeams::teams insteadof HasRoles;
        HasRoles::teams as permissionTeams;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_confirmed_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(CustomerProfile::class);
    }

    // Relationships
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    public function incomes(): HasMany
    {
        return $this->hasMany(Income::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function incomeCategories(): HasMany
    {
        return $this->hasMany(IncomeCategory::class);
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(ExpenseCategory::class);
    }

    public function budgetLimits(): HasMany
    {
        return $this->hasMany(BudgetLimit::class);
    }

    public function accountTransfers(): HasMany
    {
        return $this->hasMany(AccountTransfer::class);
    }

    public function analyticsCaches(): HasMany
    {
        return $this->hasMany(AnalyticsCache::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    // Helper Methods
    public function getTotalBalance()
    {
        return $this->accounts()->where('is_active', true)->sum('current_balance');
    }

    public function getTotalIncomeThisMonth($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $this->incomes()
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getTotalExpenseThisMonth($month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $this->expenses()
            ->whereYear('transaction_date', $year)
            ->whereMonth('transaction_date', $month)
            ->where('status', 'completed')
            ->sum('amount');
    }

    public function getNetIncomeThisMonth($month = null, $year = null)
    {
        return $this->getTotalIncomeThisMonth($month, $year) - $this->getTotalExpenseThisMonth($month, $year);
    }

    public function getExceededBudgets()
    {
        return $this->budgetLimits()
            ->where('status', 'exceeded')
            ->with('expenseCategory')
            ->get();
    }

    public function getCurrencySymbol()
    {
        $symbols = [
            'BDT' => '৳',
            'USD' => '$',
            'EUR' => '€',
            'INR' => '₹',
            'AED' => 'د.إ',
        ];

        return $symbols[$this->currency] ?? $this->currency;
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
