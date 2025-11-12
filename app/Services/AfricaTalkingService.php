<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;

class AfricaTalkingService
{
    protected $AT;

    public function __construct()
    {
        $username = env('AFRICASTALKING_USERNAME');
        $apiKey = env('AFRICASTALKING_API_KEY');
        $this->AT = new AfricasTalking($username, $apiKey);
    }

    // 📩 Send SMS
    public function sendSms($recipients, $message)
    {
        $sms = $this->AT->sms();
        $response = $sms->send([
            'to' => $recipients,
            'message' => $message
        ]);

        /**
         * $response returned by Africa's Talking SDK is an object like:
         * (object) [
         *   'SMSMessageData' => (object) [
         *     'Recipients' => [
         *        (object) ['status' => 'Success', 'number' => '+234...', ...]
         *     ]
         *   ]
         * ]
         */

        // ✅ Convert to array for easier use in controller
        return json_decode(json_encode($response), true);
    }

    // 📞 Make Voice Call
    public function makeCall($from, $to)
    {
        $voice = $this->AT->voice();
        $response = $voice->call([
            'from' => $from,
            'to' => [$to]
        ]);

        return json_decode(json_encode($response), true);
    }
}
