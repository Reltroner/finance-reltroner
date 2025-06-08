<?php
// app/Models/Transaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'transaction_code',
        'employee_id',
        'type',
        'category',
        'amount',
        'status',
        'transaction_date',
    ];

    // Optional: if you want to associate with HRM API later
    public function employee()
    {
        // Placeholder if you create local employee model in future
        return $this->belongsTo(Employee::class);
    }
}
