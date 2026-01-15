<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;
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
            'sender_id' => User::inRandomOrder()->first()?->id ?? User::factory()->create()->id,
            'tenant_id' => Tenant::factory(),
            'subject' => $this->faker->sentence(),
            'body' => $this->faker->paragraph(),
            'video_url' => $this->faker->optional(0.3)->randomElement([
                'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'https://youtu.be/dQw4w9WgXcQ',
                'https://vimeo.com/76979871',
                'https://www.dailymotion.com/video/x6tqhjm',
            ]),
            'is_broadcast' => $this->faker->boolean(20), // 20% chance of broadcast
            'sent_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'read_at' => $this->faker->optional(0.5)->dateTimeBetween('-1 month', 'now'), // 50% chance read
        ];
    }

    /**
     * Indicate that the message is a broadcast.
     */
    public function broadcast(): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => null,
            'is_broadcast' => true,
        ]);
    }
}
