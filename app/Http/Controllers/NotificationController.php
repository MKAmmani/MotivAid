<?php

namespace App\Http\Controllers;

use App\Services\AfricaTalkingService;
use Illuminate\Http\Request;
use Exception;

class NotificationController extends Controller
{
    protected $africaTalking;

    public function __construct(AfricaTalkingService $africaTalking)
    {
        $this->africaTalking = $africaTalking;
    }

    public function sendMessage()
    {
        try {
            $to = '+2347082262502'; // Replace with the recipient's phone number
            $message = 'Hello! This is a notification from your Laravel app.';
            $response = $this->africaTalking->sendSms($to, $message);

            return response()->json([
                'success' => true,
                'response' => $response,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

     public function makeVoiceCall(Request $request)
    {
        try {
            // ✅ Africa's Talking setup
            $username = env('AFRICASTALKING_USERNAME', 'sandbox');
            $apiKey = env('AFRICASTALKING_API_KEY');
            $AT = new \AfricasTalking\SDK\AfricasTalking($username, $apiKey);

            $voice = $AT->voice();

            // ✅ Destination number (from frontend or default)
            $to = $request->input('to', '+2347082262502');

            // ✅ Make the call
            $result = $voice->call([
                'from' => env('AFRICASTALKING_VOICE_NUMBER', '+2347000000000'), // optional or use your Africa's Talking number
                'to' => $to,
            ]);

            //Log::info('Voice call result:', (array) $result);

            return response()->json([
                'success' => true,
                'message' => 'Voice call initiated successfully!',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            //Log::error('Voice call failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate call: ' . $e->getMessage()
            ], 500);
        }
    }
}
