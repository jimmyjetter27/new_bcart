<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentInformation extends Model
{

    use HasFactory;

    protected $fillable = [
        'user_id',
        'bank_name',
        'bank_branch',
        'bank_acc_name',
        'bank_acc_num',
        'momo_acc_name',
        'momo_acc_number'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
