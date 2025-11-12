<?php

namespace App\Http\Controllers;


use App\Models\Patient_data;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PatientDataController extends Controller
{
    /**
     * Display a listing of the Emotiv activities.
     */
    public function index()
    {   
        return Inertia('Patient/Create');
    }

    /**
     * Store a newly created patient data in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|integer|min:1|max:120',
            'history_of_antenatal_visit' => 'required|in:yes,no',
            'gravida' => 'required|string|max:255',
            'history_of_previous_pph' => 'required|in:yes,no',
            'history_Of_ceaserian_section' => 'required|string|max:255',
            'type_of_pregenency' => 'required|in:single,twin,triplet,higher',
            'gestational_age' => 'required|string|max:255',
            // Remove hospital validation since it's automatically set from the authenticated user
        ]);
        
        $validatedData['user_id'] = auth()->id();
        
        // Automatically set hospital to the authenticated user's hospital
        $user = auth()->user();
        $validatedData['hospital'] = $user->hospital ? $user->hospital->name : 'No Hospital Assigned';

        $patient = Patient_data::create($validatedData);

        return redirect()->route('emotive.index', ['patient' => $patient->id])->with('success', 'Patient data saved successfully.');
    }
}