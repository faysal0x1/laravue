<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AccountTransfer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'from_account_id',
        'to_account_id',
        'amount',
        'description',
        'transfer_date',
        'status',
        'reference_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transfer_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
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
        return $query->whereMonth('transfer_date', now()->month)
            ->whereYear('transfer_date', now()->year);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transfer_date', [$startDate, $endDate]);
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

    public function executeTransfer()
    {
        if ($this->status === 'completed') {
            return false; // Already completed
        }

        if ($this->fromAccount->current_balance < $this->amount) {
            return false; // Insufficient balance
        }

        // Execute transfer
        $this->fromAccount->deductBalance($this->amount);
        $this->toAccount->addBalance($this->amount);

        $this->status = 'completed';
        $this->save();

        return true;
    }

    public function reverseTransfer()
    {
        if ($this->status !== 'completed') {
            return false;
        }

        $this->fromAccount->addBalance($this->amount);
        $this->toAccount->deductBalance($this->amount);

        $this->status = 'cancelled';
        $this->save();

        return true;
    }

    public function getSummary()
    {
        return "{$this->fromAccount->name} → {$this->toAccount->name}: ".
               $this->user->getCurrencySymbol().number_format($this->amount, 2);
    }
}
