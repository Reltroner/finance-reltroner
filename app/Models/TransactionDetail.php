<?php
// app/Models/TransactionDetail.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransactionDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_id',
        'line_no',
        'account_id',
        'debit',
        'credit',
        'cost_center_id',
        'memo',
    ];

    protected $casts = [
        'debit'  => 'decimal:2',
        'credit' => 'decimal:2',
    ];

    /* =======================
     * Relationships
     * ======================= */

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    /* =======================
     * Model events
     * ======================= */

    protected static function booted(): void
    {
        // Auto-assign line_no (incremental per transaction) jika belum diisi
        static::creating(function (self $detail) {
            if (empty($detail->line_no)) {
                // aman untuk concurrent insert typical app usage; jika perlu, pindah ke DB sequence/lock.
                $max = static::where('transaction_id', $detail->transaction_id)->max('line_no');
                $detail->line_no = (int) $max + 1;
            }

            // Normalisasi angka negatif: paksa >= 0
            $detail->debit  = max(0, (float) ($detail->debit  ?? 0));
            $detail->credit = max(0, (float) ($detail->credit ?? 0));
        });

        static::updating(function (self $detail) {
            $detail->debit  = max(0, (float) ($detail->debit  ?? 0));
            $detail->credit = max(0, (float) ($detail->credit ?? 0));
        });
    }

    /* =======================
     * Scopes
     * ======================= */

    public function scopeForAccount($q, int $accountId)
    {
        return $q->where('account_id', $accountId);
    }

    public function scopeForCostCenter($q, ?int $costCenterId)
    {
        return $q->when($costCenterId, fn($qq) => $qq->where('cost_center_id', $costCenterId));
    }

    public function scopeDebit($q)
    {
        return $q->where('debit', '>', 0);
    }

    public function scopeCredit($q)
    {
        return $q->where('credit', '>', 0);
    }

    public function scopeOrdered($q)
    {
        return $q->orderBy('line_no')->orderBy('id');
    }

    /* =======================
     * Accessors / Helpers
     * ======================= */

    public function getIsDebitAttribute(): bool
    {
        return (float)$this->debit > 0 && (float)$this->credit == 0.0;
    }

    public function getIsCreditAttribute(): bool
    {
        return (float)$this->credit > 0 && (float)$this->debit == 0.0;
    }

    public function getAmountAttribute(): float
    {
        // jumlah absolut pada sisi yang terisi
        return (float)$this->debit > 0 ? (float)$this->debit : (float)$this->credit;
    }

    public function getSignedAmountAttribute(): float
    {
        // debit = +, credit = -
        return (float)$this->debit - (float)$this->credit;
    }

    public function getSideAttribute(): string
    {
        return $this->is_debit ? 'debit' : ($this->is_credit ? 'credit' : 'none');
    }
}
