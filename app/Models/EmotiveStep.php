<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmotiveStep extends Model
{
    use HasFactory;

    protected $table = 'emotive_steps';

    protected $fillable = [
        'patient_data_id',
        'action',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Get the patient that owns this emotive step.
     */
    public function patient()
    {
        return $this->belongsTo(Patient_data::class, 'patient_data_id');
    }
}