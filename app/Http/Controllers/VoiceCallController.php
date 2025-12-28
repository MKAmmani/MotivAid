<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Auth;

class VoiceCallController extends Controller
{
    public function makeCall()
    {
        // Get the authenticated user
        $user = Auth::user();

        // Assuming the user has a hospital relationship with a phone number
        // You might need to adjust this depending on your actual model relationships
        $hospitalNumber = null;

        if ($user && $user->hospital) {
            $hospitalNumber = $user->hospital->contact_info; // Adjust the field name as needed
        }

        // Fallback to a default number if no hospital number is found
        if (!$hospitalNumber) {
            // You might want to throw an exception or return an error instead
            return response()->json([
                'success' => false,
                'message' => 'No hospital contact number found for the user.'
            ], 404);
        }

        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        $call = $twilio->calls->create(
            $hospitalNumber, // Dynamic hospital number
            env('TWILIO_PHONE_NUMBER'), // Twilio number
            ['url' => route('twilio.voice')] // TwiML instructions
        );

        return response()->json([
            'success' => true,
            'message' => 'Call initiated successfully!',
            'callSid' => $call->sid
        ]);
    }

    public function voiceXml()
    {
        $response = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <Response>
                <Say voice="alice">Hello Munir! This is a test call from your Laravel and Twilio setup.</Say>
            </Response>
        XML;

        return response($response, 200)->header('Content-Type', 'text/xml');
    }
}

/*namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class VoiceCallController extends Controller
{
    public function makeCall()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        $twilio->calls->create(
            '+2347082262502', // <-- The number to call (use your verified number)
            env('TWILIO_PHONE_NUMBER'),
            ['url' => route('twilio.voice')]

        );

        return "📞 Call initiated successfully!";
    }

    public function voiceXml()
    {
        $response = <<<XML
            <?xml version="1.0" encoding="UTF-8"?>
            <Response>
                <Say voice="alice">Hello Munir! This is a test call from your Laravel and Twilio setup.</Say>
            </Response>
        XML;

        return response($response, 200)->header('Content-Type', 'text/xml');
    }
}
*/