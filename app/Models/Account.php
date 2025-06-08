<?php
// app/Models/Account.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'account_name',
        'account_number',
        'balance',
        'currency',
    ];
}
