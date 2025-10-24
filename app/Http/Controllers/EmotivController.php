<?php

namespace App\Http\Controllers;

use App\Models\Emotiv;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EmotivController extends Controller
{
    /**
     * Display a listing of the Emotiv activities.
     */
    public function index()
    {
        $emotivs = Emotiv::orderBy('id', 'asc')->get();
        
        return Inertia('Emotivs/Index', [
            'emotivs' => $emotivs
        ]);
    }
}