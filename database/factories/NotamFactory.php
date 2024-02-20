<?php

namespace Database\Factories;

use App\Enum\Airports;
use App\Enum\NotamStatus;
use App\Models\Notam;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class NotamFactory extends Factory
{
    protected $model = Notam::class;

    public function definition(): array
    {
        //$airport = $this->faker->randomElement(str(Airports::ALL)->upper()->explode(',')->toArray());
        //$notamId = $this->faker->unique()->bothify('????????');
        //$id = $notamId."-$airport";

        return [
            'id'        => $this->faker->unique()->regexify('[ABC]\d{4}\/2[3-4]').'-'.$this->faker->randomElement(str(Airports::ALL)->upper()->explode(',')->toArray()),
            'structure' => function (array $attributes) {
                return [
                    'key'      => $attributes['id'],
                    'location' => substr($attributes['id'], -4),
                    'all'      => substr($attributes['id'], 0, -5)." NOTAMN\nQ) EISN/QMXLC/IV/BO /A /000/999/5325N00616W005\nA) EIDW B) 2402202300 C) 2402240600\nD) DAILY 2300-0600\nE) TWY K CLOSED\nCREATED: 16 Feb 2024 15:52:00 \nSOURCE: EUECYIYN",
                    'Created'  => $this->faker->dateTimeBetween('-1 week', '+1 week')->format('c'),
                ];
            },
            'code'       => null,
            'type'       => null,
            'summary'    => null,
            'status'     => NotamStatus::UNTAGGED,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function processing(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => NotamStatus::PROCESSING,
            ];
        });
    }

    public function tagged(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status'  => NotamStatus::TAGGED,
                'code'    => $this->faker->regexify('[ACHLPRST][1-6]'),
                'type'    => $this->faker->words(2, true),
                'summary' => $this->faker->words(7, true),
            ];
        });
    }

    public function error(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => NotamStatus::ERROR,
            ];
        });
    }
}
