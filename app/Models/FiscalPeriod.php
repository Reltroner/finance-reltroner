<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FiscalPeriod extends Model
{
    protected $fillable = [
        'year',
        'period',
        'status',
        'closed_at',
        'locked_at',
        'closed_by',
        'locked_by',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'locked_at' => 'datetime',
        'year'      => 'integer',
        'period'    => 'integer',
    ];

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isLocked(): bool
    {
        return $this->status === 'locked';
    }
}
