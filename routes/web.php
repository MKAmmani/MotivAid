<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientDataController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\EmotivController;
use App\Http\Controllers\DiagonesticController;
use App\Http\Controllers\TreatmentController;
use App\Http\Controllers\EmotiveStepController;
use App\Http\Controllers\VoiceCallController;
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


    // Hospital Routes
    Route::resource('hospitals', \App\Http\Controllers\HospitalController::class);

    Route::get('/emotive', [EmotivController::class, 'index'])->name('emotive.index');
    Route::get('/diagonestic', [DiagonesticController::class, 'index'])->name('diagonestic.index');
    Route::get('/treatment', [TreatmentController::class, 'index'])->name('treatment.index');
    Route::get('/documentation', [EmotivController::class, 'documentation'])->name('documentation.index');

    // Emotive Step Routes
    Route::post('/emotive-steps', [EmotiveStepController::class, 'store'])->name('emotive.steps.store');
    Route::get('/patients/{patientId}/summary', [EmotiveStepController::class, 'getStepsForPatient'])->name('emotive.steps.patient')->middleware('check.patient.ownership');

    // Notification Routes
    //Route::post('/send-sms', [NotificationController::class, 'sendMessage'])->name('send.sms');
    //Route::post('/make-call', [NotificationController::class, 'makeVoiceCall'])->name('make.call');

    Route::post('/make-call', [VoiceCallController::class, 'makeCall'])->name('make.call');
    //Route::get('/twilio/voice', [VoiceCallController::class, 'voiceXml'])->name('twilio.voice');
    Route::match(['get', 'post'], '/twilio/voice', [VoiceCallController::class, 'voiceXml'])->name('twilio.voice');
    /* Test route for Africa's Talking
    Route::get('/test-sms', function() {
        $service = app(\App\Services\AfricaTalkingService::class);
        $result = $service->sendSms('+2347082262502', 'Test message from MotivAid system');
        return response()->json($result);
    })->name('test.sms'); */
});

require __DIR__ . '/auth.php';
