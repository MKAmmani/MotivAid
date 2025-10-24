<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient_data extends Model
{
    use HasFactory;

    protected $table = 'patient_datas';

    protected $fillable = [
        'name',
        'age',
        'history_of_antenatal_visit',
        'gravida',
        'history_of_previous_pph',
        'history_Of_ceaserian_section',
        'type_of_pregenency',
        'gestational_age',
        'hospital',
        'is_complete',
    ];

    protected $casts = [
        'is_complete' => 'boolean',
    ];
}