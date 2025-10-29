<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class TreatmentController extends Controller
{
    public function index()
    {
        return Inertia::render('Emotive/Treatment');
    }
}
