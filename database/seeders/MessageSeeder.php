<?php

namespace Database\Seeders;

use App\Models\Message;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MessageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create an admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );

        // Get some tenants or create if none exist
        $tenants = Tenant::take(5)->get();
        if ($tenants->isEmpty()) {
            $tenants = collect();
            for ($i = 0; $i < 5; $i++) {
                $tenants->push(Tenant::factory()->create());
            }
        }

        // Sample messages
        $sampleMessages = [
            [
                'subject' => 'Bni Emma Watson',
                'body' => 'This is a sample message for Bni Emma Watson. Welcome to our platform!',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'sent_at' => '2025-06-13 10:00:00',
            ],
            [
                'subject' => 'Lorem Ipsum Watson',
                'body' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'video_url' => 'https://vimeo.com/76979871',
                'sent_at' => '2025-04-20 14:30:00',
            ],
            [
                'subject' => 'Why do we use it?',
                'body' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout.',
                'video_url' => null,
                'sent_at' => '2025-06-30 09:15:00',
            ],
            [
                'subject' => 'Variations Passages',
                'body' => 'There are many variations of passages of Lorem Ipsum available, but the majority have suffered alteration in some form.',
                'video_url' => 'https://www.dailymotion.com/video/x6tqhjm',
                'sent_at' => '2025-09-12 16:45:00',
            ],
            [
                'subject' => 'Lorem Ipsum generators',
                'body' => 'All the Lorem Ipsum generators on the Internet tend to repeat predefined chunks as necessary.',
                'video_url' => null,
                'sent_at' => '2025-12-05 11:20:00',
            ],
        ];

        foreach ($sampleMessages as $index => $messageData) {
            Message::create([
                'sender_id' => $admin->id,
                'tenant_id' => $tenants[$index % $tenants->count()]->id,
                'subject' => $messageData['subject'],
                'body' => $messageData['body'],
                'video_url' => $messageData['video_url'],
                'is_broadcast' => false,
                'sent_at' => $messageData['sent_at'],
            ]);
        }


        Message::factory(10)->create([
            'sender_id' => $admin->id,
        ]);
    }
}
