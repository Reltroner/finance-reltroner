<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Identitas jurnal
        'journal_no', 'reference', 'description',

        // Tanggal & periode
        'date', 'fiscal_year', 'fiscal_period',

        // Mata uang & kurs
        'currency_id', 'exchange_rate',

        // Total di currency transaksi
        'total_debit', 'total_credit',

        // Total terkonversi ke base currency
        'total_debit_base', 'total_credit_base',

        // Status siklus jurnal
        'status', 'posted_at', 'posted_by', 'voided_at', 'voided_by',

        // Jurnal pembalik
        'reversal_of_id',

        // Metadata
        'created_by',
    ];

    protected $casts = [
        'date'              => 'date',
        'posted_at'         => 'datetime',
        'voided_at'         => 'datetime',

        // numeric/decimal
        'exchange_rate'     => 'decimal:10',
        'total_debit'       => 'decimal:2',
        'total_credit'      => 'decimal:2',
        'total_debit_base'  => 'decimal:2',
        'total_credit_base' => 'decimal:2',
        'fiscal_year'       => 'integer',
        'fiscal_period'     => 'integer',
    ];

    /* =======================
     * Relationships
     * ======================= */

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function details()
    {
        return $this->hasMany(TransactionDetail::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function taxApplications()
    {
        return $this->hasMany(TaxApplication::class);
    }

    public function attachments()
    {
        return $this->hasMany(Attachment::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedByUser()
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function voidedByUser()
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    // reversal
    public function reversalOf()
    {
        return $this->belongsTo(Transaction::class, 'reversal_of_id');
    }

    public function reversals()
    {
        return $this->hasMany(Transaction::class, 'reversal_of_id');
    }

    /* =======================
     * Scopes (quality-of-life)
     * ======================= */

    public function scopePosted($q)
    {
        return $q->where('status', 'posted');
    }

    public function scopeDraft($q)
    {
        return $q->where('status', 'draft');
    }

    public function scopeVoided($q)
    {
        return $q->where('status', 'voided');
    }

    public function scopePeriod($q, int $year, int $period)
    {
        return $q->where('fiscal_year', $year)->where('fiscal_period', $period);
    }

    public function scopeBetweenDates($q, ?string $from, ?string $to)
    {
        return $q
            ->when($from, fn($qq) => $qq->whereDate('date', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('date', '<=', $to));
    }

    /* =======================
     * Accessors / Helpers
     * ======================= */

    // memastikan fiscal_year & fiscal_period auto-terisi dari 'date' bila belum diset
    protected function date(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                $this->attributes['date'] = $value;

                // jika user belum set fiscal_* maka turunkan dari tanggal
                try {
                    $dt = \Illuminate\Support\Carbon::parse($value);
                    $this->attributes['fiscal_year']   = $this->attributes['fiscal_year']   ?? (int)$dt->format('Y');
                    $this->attributes['fiscal_period'] = $this->attributes['fiscal_period'] ?? (int)$dt->format('n'); // 1..12
                } catch (\Throwable $e) {
                    // biarkan validator/controller yang menangani jika tanggal invalid
                }

                return $value;
            }
        );
    }

    public function getIsBalancedAttribute(): bool
    {
        return (round($this->total_debit, 2) === round($this->total_credit, 2))
            && (round($this->total_debit_base, 2) === round($this->total_credit_base, 2));
    }

    public function getBalanceDiffAttribute(): float
    {
        return round((float)$this->total_debit - (float)$this->total_credit, 2);
    }

    public function getBalanceDiffBaseAttribute(): float
    {
        return round((float)$this->total_debit_base - (float)$this->total_credit_base, 2);
    }

    /* =======================
     * Domain helpers (opsional)
     * ======================= */

    public function markPosted(int $userId): void
    {
        $this->forceFill([
            'status'    => 'posted',
            'posted_at' => now(),
            'posted_by' => $userId,
        ])->save();
    }

    public function markVoided(int $userId): void
    {
        $this->forceFill([
            'status'   => 'voided',
            'voided_at'=> now(),
            'voided_by'=> $userId,
        ])->save();
    }

    /**
     * Generator sederhana: override di service/observer jika perlu pola custom.
     */
    public static function generateJournalNo(?int $year = null, ?int $period = null): string
    {
        $year   = $year   ?? (int) now()->format('Y');
        $period = $period ?? (int) now()->format('n');
        $seq    = str_pad((string) (static::where('fiscal_year', $year)
                        ->where('fiscal_period', $period)->count() + 1), 6, '0', STR_PAD_LEFT);

        return "JV-{$year}-" . str_pad((string)$period, 2, '0', STR_PAD_LEFT) . "-{$seq}";
    }
}
