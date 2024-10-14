<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreativeCategory extends Model
{

    use HasFactory;

    protected $fillable = ['image_public_id', 'image_url', 'creative_category'];
}
