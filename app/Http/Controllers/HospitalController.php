<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
//use Illuminate\Http\RedirectResponse;

use Inertia\Inertia;

class HospitalController extends Controller
{
    /**
     * Display a listing of the hospitals.
     */
    public function index()
    {
        $hospitals = Hospital::all();
        return Inertia('Hospitals/Index');
        //return view('hospitals.index', compact('hospitals'));
    }

    /**
     * Show the form for creating a new hospital.
     */
    public function create()
    {
        return Inertia('Hospitals/Create');
    }

    /**
     * Store a newly created hospital in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ]);

        Hospital::create($request->all());

        return redirect()->route('Hospitals/Index')
            ->with('success', 'Hospital created successfully.');
    }

    /**
     * Display the specified hospital.
     */
    public function show(Hospital $hospital)
    {
        return view('Hospitals/Show', compact('hospital'));
    }

    /**
     * Show the form for editing the specified hospital.
     */
    public function edit(Hospital $hospital)
    {
        return view('Hospitals/Edit', compact('hospital'));
    }

    /**
     * Update the specified hospital in storage.
     */
    public function update(Request $request, Hospital $hospital)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ]);

        $hospital->update($request->all());

        return redirect()->route('Hospitals/index')
            ->with('success', 'Hospital updated successfully.');
    }

    /**
     * Remove the specified hospital from storage.
     */
    public function destroy(Hospital $hospital)
    {
        $hospital->delete();

        return redirect()->route('Hospitals/Index')
            ->with('success', 'Hospital deleted successfully.');
    }
}
