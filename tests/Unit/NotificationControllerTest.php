<?php

namespace Tests\Unit;

use App\Http\Controllers\NotificationController;
use App\Services\AfricaTalkingService;
use Illuminate\Http\Request;
use Tests\TestCase;
use Mockery;

class NotificationControllerTest extends TestCase
{
    public function test_send_message_returns_json_response()
    {
        // Mock the AfricaTalkingService
        $mockAfricaTalking = Mockery::mock(AfricaTalkingService::class);
        $mockAfricaTalking->shouldReceive('sendSms')
            ->with('+2347082262502', 'Hello! This is a notification from your Laravel app.')
            ->andReturn([
                'success' => true,
                'message' => 'Message sent successfully',
                'data' => [],
                'recipients_count' => 1,
                'cost' => 'KES 0.80'
            ]);

        $controller = new NotificationController($mockAfricaTalking);

        $request = new Request();
        $response = $controller->sendMessage($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('SMS sent successfully', $data['message']);
    }

    public function test_make_voice_call_returns_json_response()
    {
        // Mock the AfricaTalkingService
        $mockAfricaTalking = Mockery::mock(AfricaTalkingService::class);
        $mockAfricaTalking->shouldReceive('makeCall')
            ->with('+2347082262502', '+2347035689277')
            ->andReturn([
                'success' => true,
                'message' => 'Call initiated successfully',
                'data' => [],
                'call_session_id' => 'session123'
            ]);

        $controller = new NotificationController($mockAfricaTalking);

        $request = new Request();
        $response = $controller->makeVoiceCall($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertTrue($data['success']);
        $this->assertEquals('Voice call initiated successfully', $data['message']);
    }

    public function test_send_message_handles_exception()
    {
        // Mock the AfricaTalkingService to throw an exception
        $mockAfricaTalking = Mockery::mock(AfricaTalkingService::class);
        $mockAfricaTalking->shouldReceive('sendSms')
            ->andThrow(new \Exception('API Error'));

        $controller = new NotificationController($mockAfricaTalking);

        $request = new Request();
        $response = $controller->sendMessage($request);

        $this->assertEquals(500, $response->getStatusCode());
        $this->assertJson($response->getContent());
        
        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertStringContainsString('API Error', $data['message']);
    }
}