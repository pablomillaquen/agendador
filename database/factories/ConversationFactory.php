<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => \App\Models\Client::factory(),
            'channel' => 'whatsapp',
            'started_at' => fake()->dateTimeBetween('-1 week', 'now'),
            'ended_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-1 week', 'now') : null,
        ];
    }
}
