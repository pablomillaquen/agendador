<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\BusinessHour;
use App\Models\Client;
use App\Models\Conversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_n8n_flow()
    {
        // 1. Create Client (simulating N8N first contact)
        $clientData = [
            'name' => 'John Doe',
            'phone' => '+1234567890',
            'email' => 'john@example.com',
        ];

        $response = $this->postJson('/api/clients', $clientData);
        $response->assertStatus(201);
        $clientId = $response->json('id');
        $this->assertDatabaseHas('clients', ['phone' => '+1234567890']);

        // 2. Setup Business Hours (Admin setup)
        BusinessHour::create(['day_of_week' => 1, 'start_time' => '09:00', 'end_time' => '17:00']); // Monday

        // 3. Application: Check Availability for next Monday
        $nextMonday = date('Y-m-d', strtotime('next monday'));
        $availabilityResponse = $this->getJson("/api/availability?date=$nextMonday&duration=60");
        $availabilityResponse->assertStatus(200);
        $slots = $availabilityResponse->json('slots');
        $this->assertNotEmpty($slots);
        $firstSlot = $slots[0]; // e.g., "09:00"

        // 4. Create Appointment
        $apptData = [
            'client_id' => $clientId,
            'start_at' => "$nextMonday $firstSlot",
            'end_at' => date('Y-m-d H:i:s', strtotime("$nextMonday $firstSlot") + 3600),
            'status' => 'scheduled',
            'notes' => 'Consultation via N8N',
        ];

        $apptResponse = $this->postJson('/api/appointments', $apptData);
        $apptResponse->assertStatus(201);
        $this->assertDatabaseHas('appointments', ['client_id' => $clientId]);

        // 5. Verify Slot is now taken
        $availabilityResponse2 = $this->getJson("/api/availability?date=$nextMonday&duration=60");
        $slots2 = $availabilityResponse2->json('slots');
        // The first slot should NOT be in the list anymore
        $this->assertNotContains($firstSlot, $slots2);

        // 6. Log Conversation & Message
        $convResponse = $this->postJson('/api/conversations', [
            'client_id' => $clientId,
            'channel' => 'whatsapp',
            'messages' => [
                ['sender' => 'client', 'content' => 'Hello', 'timestamp' => now()->toDateTimeString()],
                ['sender' => 'bot', 'content' => 'Hi there', 'timestamp' => now()->addSecond()->toDateTimeString()],
            ]
        ]);
        $convResponse->assertStatus(201);
        $convId = $convResponse->json('id');
        $this->assertDatabaseHas('conversations', ['id' => $convId]);
        $this->assertDatabaseCount('messages', 2);
    }
}
