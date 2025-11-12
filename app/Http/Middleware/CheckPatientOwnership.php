<?php

namespace App\Http\Middleware;

use App\Models\Patient_data;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPatientOwnership
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get the patient ID from route parameters or query parameters
        $patientId = $request->route('patient') ?? $request->route('id') ?? $request->query('patient');
        
        if ($patientId) {
            // Find the patient record
            $patient = Patient_data::find($patientId);
            
            // If the patient record doesn't exist, return 404
            if (!$patient) {
                abort(404, 'Patient record not found.');
            }
            
            // Check if the authenticated user owns this patient record
            if ($patient->user_id !== Auth::id()) {
                abort(403, 'You do not have permission to access this patient record.');
            }
        }
        
        return $next($request);
    }
}
