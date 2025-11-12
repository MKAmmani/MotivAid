<?php

namespace App\Http\Controllers;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HospitalController extends Controller
{
    /**
     * Display a listing of the hospitals.
     */
    public function index(): View
    {
        $hospitals = Hospital::all();
        return view('hospitals.index', compact('hospitals'));
    }

    /**
     * Show the form for creating a new hospital.
     */
    public function create(): View
    {
        return view('hospitals.create');
    }

    /**
     * Store a newly created hospital in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ]);

        Hospital::create($request->all());

        return redirect()->route('hospitals.index')
                         ->with('success', 'Hospital created successfully.');
    }

    /**
     * Display the specified hospital.
     */
    public function show(Hospital $hospital): View
    {
        return view('hospitals.show', compact('hospital'));
    }

    /**
     * Show the form for editing the specified hospital.
     */
    public function edit(Hospital $hospital): View
    {
        return view('hospitals.edit', compact('hospital'));
    }

    /**
     * Update the specified hospital in storage.
     */
    public function update(Request $request, Hospital $hospital): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'contact_info' => 'nullable|string|max:255',
        ]);

        $hospital->update($request->all());

        return redirect()->route('hospitals.index')
                         ->with('success', 'Hospital updated successfully.');
    }

    /**
     * Remove the specified hospital from storage.
     */
    public function destroy(Hospital $hospital): RedirectResponse
    {
        $hospital->delete();

        return redirect()->route('hospitals.index')
                         ->with('success', 'Hospital deleted successfully.');
    }
}
