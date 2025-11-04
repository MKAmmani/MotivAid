<?php

namespace App\Http\Controllers;

use App\Models\Emotiv;
use App\Models\Patient_data;
use App\Models\EmotiveStep;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmotivController extends Controller
{
    /**
     * Display a listing of the Emotiv activities.
     */
    public function index(Request $request)
    {
        // Get patient ID from request
        $patientId = $request->query('patient');
        
        // Define the E-motive steps for postpartum hemorrhage management
        $emotiveSteps = [
            [
                'id' => 1,
                'title' => 'Early identification of bleeding',
                'description' => 'Promptly identify and monitor blood loss. Initiate E-MOTIVE protocol if blood loss is 500ml or less.',
                'completed' => false
            ],
            [
                'id' => 2,
                'title' => 'Massaging of uterus',
                'description' => '',
                'completed' => false
            ],
            [
                'id' => 3,
                'title' => 'Administer Oxytocin',
                'description' => 'Administer 10IU of oxytocin (I.M)',
                'completed' => false
            ],
            [
                'id' => 4,
                'title' => 'Administer Tranexamic Acid',
                'description' => '1g of tranexamic acid (I.V)',
                'completed' => false
            ],
            [
                'id' => 5,
                'title' => 'IV fluids',
                'description' => 'Administer N/S or R/L with 10IU of oxytonic',
                'completed' => false
            ],
            [
                'id' => 6,
                'title' => 'Vital signs monitoring',
                'description' => '',
                'completed' => false
            ],
            [
                'id' => 7,
                'title' => 'Examination / Escalation',
                'description' => '',
                'completed' => false
            ]
        ];
        
        return Inertia('Emotive/Index', [
            'emotiveSteps' => $emotiveSteps,
            'patientId' => $patientId
        ]);
    }

    /**
     * Display the documentation page.
     */
    public function documentation(Request $request)
    {
        // Get the authenticated user
        $user = $request->user();
        
        // Get paginated patients treated by this user (10 per page)
        $patients = Patient_data::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return Inertia::render('Emotive/Documentation', [
            'patients' => $patients->items(),
            'meta' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'per_page' => $patients->perPage(),
                'total' => $patients->total(),
                'from' => $patients->firstItem(),
                'to' => $patients->lastItem(),
            ]
        ]);
    }
}