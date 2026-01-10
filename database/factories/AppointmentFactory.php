<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('now', '+1 month');
        return [
            'client_id' => \App\Models\Client::factory(),
            'start_at' => $start,
            'end_at' => (clone $start)->modify('+30 minutes'),
            'status' => 'scheduled',
            'notes' => fake()->sentence(),
        ];
    }
}
