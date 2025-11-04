<?php

namespace App\Http\Controllers;

use App\Services\AfricaTalkingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    protected $africaTalking;

    public function __construct(AfricaTalkingService $africaTalking)
    {
        $this->africaTalking = $africaTalking;
    }

    public function sendMessage(Request $request)
    {
        try {
            $to = $request->input('to', '+2347082262502'); // Default recipient phone number
            $message = $request->input('message', 'Hello! This is a notification from your Laravel app.');
            
            // Validate inputs
            if (empty($to) || empty($message)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Recipient phone number and message are required'
                ], 400);
            }
            
            $response = $this->africaTalking->sendSms($to, $message);
            
            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'SMS sent successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error sending SMS: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send SMS: ' . $e->getMessage()
            ], 500);
        }
    }

    public function makeVoiceCall(Request $request)
    {
        try {
            $from = $request->input('from', '+2347082262502'); // Default from number
            $to = $request->input('to', '+2347035689277');     // Default to number
            
            // Validate inputs
            if (empty($from) || empty($to)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Both from and to phone numbers are required'
                ], 400);
            }
            
            $response = $this->africaTalking->makeCall($from, $to);
            
            return response()->json([
                'success' => true,
                'data' => $response,
                'message' => 'Voice call initiated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error making voice call: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to make voice call: ' . $e->getMessage()
            ], 500);
        }
    }
}
