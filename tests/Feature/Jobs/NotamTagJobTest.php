<?php

use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Jobs\NotamTagJob;
use App\Models\Notam;
use GuzzleHttp\Exception\InvalidArgumentException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

it('marks a notam status as ERROR if openai is unable to return valid data', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::on(fn ($message) => str_contains($message, 'Tagger Error:')));

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'role'    => 'assistant',
                        'content' => 'non valid json data',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
        ]),
    ]);

    $notam = Notam::factory()->processing()->create();
    $job = new NotamTagJob($notam, LLM::GPT_3_5_TURBO);
    $job->tries = 1; //When using the sync driver we can never have more than 1 try so we have to override the job class

    dispatch_sync($job);
    $notam->refresh();

    expect($notam->status)->toBe(NotamStatus::ERROR);
});

it('it marks a notam status as ERROR if openai keeps returning a finish reason other than stop', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::on(fn ($message) => str_contains($message, 'Tagger Error: Open AI finish reason was length')));

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'role'    => 'assistant',
                        'content' => '[{"key": "A0407/24-EIDW","type": "Runway closed", "code": "R3", "summary": "Runway 16/34 closed for takeoff and landing"}]',
                    ],
                    'finish_reason' => 'length',
                ],
            ],
        ]),
    ]);

    $notam = Notam::factory()->processing()->create();
    $job = new NotamTagJob($notam, LLM::GPT_3_5_TURBO);
    $job->tries = 1; //When using the sync driver we can never have more than 1 try so we have to override the job class

    dispatch_sync($job);
    $notam->refresh();

    expect($notam->status)->toBe(NotamStatus::ERROR);
});

it('it marks a notam status as UNTAGGED if we are unable to connect to openai', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::on(fn ($message) => str_contains($message, 'Tagger Connection Issue')));

    //Workaround because normal OpenAi class was marked final.
    $mock = Mockery::mock(stdClass::class);
    $mock->shouldReceive('chat')->andThrow(new TransporterException(new InvalidArgumentException()));
    app()->instance('openai', $mock);

    $notam = Notam::factory()->processing()->create();
    expect($notam->status)->toBe(NotamStatus::PROCESSING);

    $job = new NotamTagJob($notam, LLM::GPT_3_5_TURBO);
    $job->tries = 1; //When using the sync driver we can never have more than 1 try so we have to override the job class

    dispatch_sync($job);
    $notam->refresh();

    expect($notam->status)->toBe(NotamStatus::UNTAGGED);
});
