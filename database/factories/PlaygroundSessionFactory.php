<?php

namespace Database\Factories;

use App\Models\PlaygroundSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaygroundSessionFactory extends Factory
{
    protected $model = PlaygroundSession::class;

    public function definition(): array
    {
        return [
            'ip_address' => $this->faker->ipv4,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
