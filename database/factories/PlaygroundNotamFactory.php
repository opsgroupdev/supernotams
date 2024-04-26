<?php

namespace Database\Factories;

use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Enum\Tag;
use App\Models\PlaygroundNotam;
use App\Models\PlaygroundSession;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaygroundNotamFactory extends Factory
{
    protected $model = PlaygroundNotam::class;

    public function definition(): array
    {
        return [
            'session_id' => PlaygroundSession::factory(),
            'text'       => $this->faker->words(50, true),
            'tag'        => null,
            'summary'    => null,
            'llm'        => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function processed(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'tag'          => $this->faker->randomElement(Tag::class)->name,
                'summary'      => $this->faker->words(7, true),
                'llm'          => $this->faker->randomElement(LLM::class)->value,
                'status'       => NotamStatus::TAGGED,
                'processed_at' => now(),
            ];
        });
    }
}
