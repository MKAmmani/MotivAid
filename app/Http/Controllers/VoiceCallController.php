<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Twilio\Rest\Client;

class VoiceCallController extends Controller
{
    public function makeCall()
    {
        $sid = env('TWILIO_SID');
        $token = env('TWILIO_AUTH_TOKEN');
        $twilio = new Client($sid, $token);

        $call = $twilio->calls->create(
            '+2347082262502', // your verified number
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