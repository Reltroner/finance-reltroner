<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'reference', 'description', 'date', 'currency_id', 
        'total_debit', 'total_credit', 'created_by'
    ];

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
}
