<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Currency extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'symbol', 'rate'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
