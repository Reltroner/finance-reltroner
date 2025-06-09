<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'address', 'phone', 'email', 'tax_number'
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
