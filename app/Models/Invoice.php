<?php
// app/Models/Invoice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_code',
        'employee_id',
        'description',
        'amount',
        'status',
        'due_date',
    ];
}
