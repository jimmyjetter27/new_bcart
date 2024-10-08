<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoTag extends Model
{

    use HasFactory;

    protected $fillable = ['name'];
    public function photos()
    {
        return $this->belongsToMany(Photo::class, 'photo_tag', 'photo_tag_id', 'photo_id');
    }

}
