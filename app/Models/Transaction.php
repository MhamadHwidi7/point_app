<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_account_number',
        'receiver_account_number',
        'sender_card_number',
        'amount',
        'purpose',
        'fee',
        'reference_number',
    ];
}
