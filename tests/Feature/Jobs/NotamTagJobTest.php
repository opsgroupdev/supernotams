<?php

use App\Enum\NotamStatus;
use App\Jobs\NotamTagJob;
use App\Models\Notam;
use GuzzleHttp\Exception\InvalidArgumentException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

it('sends a notam to the live server to get decoded', function () {
    $json = json_decode(file_get_contents(base_path('tests/source/EIDW_Notams.json')), true);

    $notam = Notam::create([
        'id'        => $json[9]['key'],
        'structure' => json_encode($json[9]),
        'status'    => NotamStatus::UNTAGGED,
    ]);

    NotamTagJob::dispatchSync($notam);
    $notam->refresh();

    $this->assertModelExists($notam);
    expect($notam->status)->toBe(NotamStatus::TAGGED);
    expect($notam->code)->not()->toBeNull()->toContain('R');
    expect($notam->type)->not()->toBeNull()->toContain('Runway');
})
    ->skip();

it('uses openai fake facade to prevent hitting the live server', function () {
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'role'    => 'assistant',
                        'content' => '{"key": "A0407/24-EIDW","type": "Runway closed","code": "R1","summary": "Runway 16/34 closed for takeoff and landing"}',
                    ],
                    'finishReason' => 'stop',
                ],
            ],
        ]),
    ]);
    Log::shouldReceive('info')->once();

    $notam = Notam::factory()->processing()->create();
    expect($notam->status)->toBe(NotamStatus::PROCESSING);

    NotamTagJob::dispatchSync($notam);
    $notam->refresh();

    $this->assertModelExists($notam);
    expect($notam->status)->toBe(NotamStatus::TAGGED);
    expect($notam->code)->toBe('R1');
    expect($notam->type)->toBe('Runway closed');
    expect($notam->summary)->toBe('Runway 16/34 closed for takeoff and landing');
});

it('it marks a notam status as ERROR if openai is unable to return valid data', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::on(fn ($message) => str_contains($message, 'OpenAI Error:')));

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'role'    => 'assistant',
                        'content' => 'non valid json data',
                    ],
                    'finishReason' => 'stop',
                ],
            ],
        ]),
    ]);

    $notam = Notam::factory()->processing()->create();
    expect($notam->status)->toBe(NotamStatus::PROCESSING);

    $job = new NotamTagJob($notam);
    $job->tries = 1; //When using the sync driver we can never have more than 1 try so we have to override the job class

    dispatch_sync($job);
    $notam->refresh();

    expect($notam->status)->toBe(NotamStatus::ERROR);
});

it('it marks a notam status as ERROR if openai keeps returning a finish reason other than stop', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::on(fn ($message) => str_contains($message, 'OpenAI Error:')));

    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'role'    => 'assistant',
                        'content' => '[{"key": "A0407/24-EIDW","type": "Runway closed","code": "R1","summary": "Runway 16/34 closed for takeoff and landing"}]',
                    ],
                    'finishReason' => 'length',
                ],
            ],
        ]),
    ]);

    $notam = Notam::factory()->processing()->create();
    expect($notam->status)->toBe(NotamStatus::PROCESSING);

    $job = new NotamTagJob($notam);
    $job->tries = 1; //When using the sync driver we can never have more than 1 try so we have to override the job class

    dispatch_sync($job);
    $notam->refresh();

    expect($notam->status)->toBe(NotamStatus::ERROR);
});

it('it marks a notam status as UNPROCESSED if we are unable to connect to openai', function () {
    Log::shouldReceive('error')
        ->once()
        ->with(Mockery::on(fn ($message) => str_contains($message, 'OpenAI Connection Issue')));

    //Workaround because normal OpenAi class was marked final.
    $mock = Mockery::mock(stdClass::class);
    $mock->shouldReceive('chat')->andThrow(new TransporterException(new InvalidArgumentException()));
    app()->instance('openai', $mock);

    $notam = Notam::factory()->processing()->create();
    expect($notam->status)->toBe(NotamStatus::PROCESSING);

    $job = new NotamTagJob($notam);
    $job->tries = 1; //When using the sync driver we can never have more than 1 try so we have to override the job class

    dispatch_sync($job);
    $notam->refresh();

    expect($notam->status)->toBe(NotamStatus::UNTAGGED);
});
