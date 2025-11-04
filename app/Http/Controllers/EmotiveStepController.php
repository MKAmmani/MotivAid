<?php

namespace App\Http\Controllers;

use App\Models\EmotiveStep;
use App\Models\Patient_data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class EmotiveStepController extends Controller
{
    /**
     * Store a newly completed Emotive step.
     */
    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patient_datas,id',
            'action' => 'required|string|max:255',
            'completed_at' => 'required|date_format:Y-m-d H:i:s',
        ]);

        // Verify the patient belongs to the current user (if you have user-patient relationships)
        // For now, we'll just create the step
        $emotiveStep = EmotiveStep::create([
            'patient_data_id' => $request->patient_id,
            'action' => $request->action,
            'completed_at' => $request->completed_at,
        ]);

        //return response()->json(['success' => true, 'step' => $emotiveStep]);
    }

    /**
     * Get all Emotive steps for a specific patient.
     */
    public function getStepsForPatient($patientId)
    {
        // Verify the patient exists and get their steps
        $patient = Patient_data::findOrFail($patientId);
        
        $steps = EmotiveStep::where('patient_data_id', $patientId)
            ->orderBy('completed_at', 'asc')
            ->get();

        //return response()->json(['steps' => $steps]);
        return Inertia::render('Emotive/summary', [
            'steps' => $steps,
            'patient' => $patient,
        ]);
    }
}