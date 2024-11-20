<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['card_number', 'card_holder', 'expiry_date', 'card_type','cvv', 'amount', 'is_successful'];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
