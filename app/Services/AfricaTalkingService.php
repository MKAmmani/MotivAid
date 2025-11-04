<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class AfricaTalkingService
{
    protected $AT;
    protected $sms;
    protected $voice;

    public function __construct()
    {
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');

        // Validate credentials are provided
        if (empty($username) || empty($apiKey)) {
            Log::error('Africa\'s Talking credentials are missing');
            throw new InvalidArgumentException('Africa\'s Talking username and API key are required');
        }

        try {
            $this->AT = new AfricasTalking($username, $apiKey);
            $this->sms = $this->AT->sms();
            $this->voice = $this->AT->voice();
        } catch (\Exception $e) {
            Log::error('Failed to initialize Africa\'s Talking service: ' . $e->getMessage());
            throw new \Exception('Failed to initialize Africa\'s Talking service: ' . $e->getMessage());
        }
    }

    /**
     * Send SMS to one or more recipients
     * 
     * @param string|array $recipients Phone number(s) in international format (e.g. +254712345678)
     * @param string $message The message to send
     * @param string|null $from The short code or sender ID to use (optional)
     * @return array
     * @throws InvalidArgumentException
     */
    public function sendSms($recipients, $message, $from = null)
    {
        try {
            // Validate inputs
            $this->validateSmsInputs($recipients, $message);
            
            $options = [
                'to' => $recipients,
                'message' => $message
            ];
            
            if (!empty($from)) {
                $options['from'] = $from;
            }

            $result = $this->sms->send($options);
            
            // Process the response to return a more structured result
            $processedResult = $this->processSmsResponse($result);
            
            Log::info('SMS sent successfully', [
                'recipients' => $recipients,
                'message_length' => strlen($message),
                'response' => $processedResult
            ]);

            return $processedResult;
        } catch (\Exception $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage(), [
                'recipients' => $recipients,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to send SMS: ' . $e->getMessage());
        }
    }

    /**
     * Make a voice call
     * 
     * @param string $from The phone number to call from (should be a verified number)
     * @param string $to The phone number to call to
     * @return array
     * @throws InvalidArgumentException
     */
    public function makeCall($from, $to)
    {
        try {
            // Validate phone numbers
            $this->validatePhoneNumber($from, 'from');
            $this->validatePhoneNumber($to, 'to');

            // According to Africa's Talking docs, for voice calls the 'to' field should be a comma-separated string
            $result = $this->voice->call([
                'from' => $from,
                'to' => $to  // Voice calls take a single number or comma-separated string, not an array
            ]);

            // Process the response to return a more structured result
            $processedResult = $this->processCallResponse($result);

            Log::info('Voice call initiated successfully', [
                'from' => $from,
                'to' => $to,
                'response' => $processedResult
            ]);

            return $processedResult;
        } catch (\Exception $e) {
            Log::error('Failed to make voice call: ' . $e->getMessage(), [
                'from' => $from,
                'to' => $to,
                'error' => $e->getMessage()
            ]);
            
            throw new \Exception('Failed to make voice call: ' . $e->getMessage());
        }
    }

    /**
     * Process SMS response to return a structured result
     * 
     * @param mixed $response The raw response from Africa's Talking API
     * @return array
     */
    private function processSmsResponse($response)
    {
        if (is_array($response) && isset($response['data'])) {
            $data = $response['data'];
            $success = !empty($data['SMSMessageData']) && $data['SMSMessageData']['Message'] !== 'Error';
            
            return [
                'success' => $success,
                'message' => $data['SMSMessageData']['Message'] ?? ($success ? 'Message sent successfully' : 'Failed to send message'),
                'data' => $data,
                'recipients_count' => count($data['SMSMessageData']['Recipients'] ?? []),
                'cost' => $data['SMSMessageData']['Cost'] ?? null,
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Unexpected response format',
            'data' => $response,
            'recipients_count' => 0,
            'cost' => null,
        ];
    }

    /**
     * Process call response to return a structured result
     * 
     * @param mixed $response The raw response from Africa's Talking API
     * @return array
     */
    private function processCallResponse($response)
    {
        if (is_array($response) && isset($response['data'])) {
            $data = $response['data'];
            $success = !empty($data['entries']) || !empty($data['entry']);
            
            return [
                'success' => $success,
                'message' => $success ? 'Call initiated successfully' : 'Failed to initiate call',
                'data' => $data,
                'call_session_id' => $data['entries'][0]['callSessionId'] ?? $data['entry'][0]['callSessionId'] ?? null,
            ];
        }
        
        return [
            'success' => false,
            'message' => 'Unexpected response format',
            'data' => $response,
            'call_session_id' => null,
        ];
    }

    /**
     * Validate SMS inputs
     * 
     * @param string|array $recipients
     * @param string $message
     * @return void
     * @throws InvalidArgumentException
     */
    private function validateSmsInputs($recipients, $message)
    {
        // Validate recipients
        if (empty($recipients)) {
            throw new InvalidArgumentException('Recipients cannot be empty');
        }

        $recipientList = is_array($recipients) ? $recipients : [$recipients];
        
        foreach ($recipientList as $recipient) {
            $this->validatePhoneNumber($recipient, 'recipient');
        }

        // Validate message
        if (empty(trim($message))) {
            throw new InvalidArgumentException('Message cannot be empty');
        }

        if (strlen($message) > 1600) { // Most SMS gateways have limits around 160 chars per segment, but allow multiple segments
            throw new InvalidArgumentException('Message exceeds maximum length (1600 characters)');
        }
    }

    /**
     * Validate a phone number
     * 
     * @param string $number
     * @param string $field
     * @return void
     * @throws InvalidArgumentException
     */
    private function validatePhoneNumber($number, $field = 'phone number')
    {
        if (empty($number)) {
            throw new InvalidArgumentException("{$field} cannot be empty");
        }

        // Remove any spaces, dashes, or parentheses
        $cleanNumber = preg_replace('/[\s\-\(\)]/', '', $number);

        // Check if it starts with + followed by country code, or just numbers
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $cleanNumber)) {
            throw new InvalidArgumentException("Invalid {$field}: {$number}. Must be in international format (e.g. +254712345678)");
        }

        // Ensure it's in the international format with +
        if ($cleanNumber[0] !== '+') {
            $cleanNumber = '+' . $cleanNumber;
        }

        // Validate the format again after adding +
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $cleanNumber)) {
            throw new InvalidArgumentException("Invalid {$field}: {$number}. Must be in international format (e.g. +254712345678)");
        }
    }
}
