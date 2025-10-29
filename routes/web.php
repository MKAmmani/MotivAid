<?php

use App\Http\Controllers\PatientDataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmotivController;
use App\Http\Controllers\DiagonesticController;
use App\Http\Controllers\TreatmentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    Route::get('/new-patient', [PatientDataController::class, 'index'])->name('patient.index');
    Route::post('/new-patient', [PatientDataController::class, 'store'])->name('patient.store');


    Route::get('/emotive', [EmotivController::class, 'index'])->name('emotive.index');
    Route::get('/diagonestic', [DiagonesticController::class, 'index'])->name('diagonestic.index');
    Route::get('/treatment', [TreatmentController::class, 'index'])->name('treatment.index');
    Route::get('/documentation', [EmotivController::class, 'documentation'])->name('documentation.index');
});

require __DIR__.'/auth.php';
