<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['card_number', 'card_holder', 'card_type', 'expiry_date', 'cvv', 'amount', 'status'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
