<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Message>
 */
class MessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => \App\Models\Conversation::factory(),
            'sender' => fake()->randomElement(['client', 'bot']),
            'content' => fake()->sentence(),
            'timestamp' => fake()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
