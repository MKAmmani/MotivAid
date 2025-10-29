<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DiagonesticController extends Controller
{
    /**
     * Display the diagnostic page.
     */
    public function index()
    {
        return Inertia::render('Emotive/Diagonestic');
    }
}
