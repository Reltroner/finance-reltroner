<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaxApplication extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tax_id', 'transaction_id', 'amount'
    ];

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
