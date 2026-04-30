<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    public function definition(): array
    {
        $username = $this->faker->unique()->userName();
        $archetypes = ['pet_parent', 'home_cook', 'fitness', 'new_parent', 'gardener',
            'commuter', 'remote_worker', 'student', 'traveler', 'diy_maker',
            'book_reader', 'gamer', 'music_fan', 'weather_watcher', 'sports_fan'];

        return [
            'display_name'      => $this->faker->name(),
            'username'          => $username,
            'bio'               => $this->faker->sentence(),
            'avatar_url'        => "https://api.dicebear.com/9.x/bottts-neutral/svg?seed={$username}",
            'joined_at'         => $this->faker->dateTimeBetween('-6 months', 'now'),
            'archetype'         => $this->faker->randomElement($archetypes),
            'posting_frequency' => $this->faker->randomElement(['power', 'moderate', 'casual']),
        ];
    }
}
