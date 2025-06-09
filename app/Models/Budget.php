<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'account_id', 'year', 'month', 'amount', 'actual'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
