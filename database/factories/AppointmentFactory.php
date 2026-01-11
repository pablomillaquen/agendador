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
        $date = fake()->dateTimeBetween('now', '+1 month');
        $hour = rand(9, 16);
        $minute = [0, 15, 30, 45][rand(0, 3)];
        
        $start = \Carbon\Carbon::instance($date)->setHour($hour)->setMinute($minute)->setSecond(0);
        
        return [
            'client_id' => \App\Models\Client::factory(),
            'start_at' => $start,
            'end_at' => (clone $start)->addMinutes(30),
            'status' => 'scheduled',
            'notes' => fake()->sentence(),
        ];
    }
}
