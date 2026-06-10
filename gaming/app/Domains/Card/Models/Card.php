<?php

namespace App\Domains\Card\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['suit', 'face', 'value'];
}
