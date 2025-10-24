<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Emotiv extends Model
{
    use HasFactory;

    protected $table = 'emotivs';

    protected $fillable = [
        'name',
        'description',
    ];
}