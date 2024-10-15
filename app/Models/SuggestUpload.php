<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuggestUpload extends Model
{

    use HasFactory;

    protected $fillable = ['user_id', 'suggestion'];

    public function suggester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
