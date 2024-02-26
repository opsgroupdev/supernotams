<?php

use App\Actions\NotamOpenAiTagger;
use App\Enum\LLM;
use App\Enum\NotamStatus;
use App\Exceptions\TaggingConnectionException;
use App\Models\Notam;
use GuzzleHttp\Exception\InvalidArgumentException;
use OpenAI\Exceptions\TransporterException;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Responses\Chat\CreateResponse;

it('sends a notam to the live server to get decoded', function () {
    $notams = json_decode(file_get_contents(base_path('tests/source/icao_sample.json')), true);

    $notam = Notam::create([
        'id'       => $notams[9]['key'],
        'fullText' => $notams[9]['all'],
        'source'   => $notams[9],
        'status'   => NotamStatus::UNTAGGED,
    ]);

    $tagger = new NotamOpenAiTagger();
    $tagger->tag($notam);

    $this->assertModelExists($notam);
    expect($notam->status)->toBe(NotamStatus::TAGGED);
    expect($notam->code)->not()->toBeNull()->toContain('R');
    expect($notam->type)->not()->toBeNull()->toContain('Runway');
})
    ->skip();

it('tags and logs a notam correctly', function () {
    $notam = Notam::factory()->processing()->create();
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'role'    => 'assistant',
                        'content' => '{"id": "'.$notam->id.'","type": "Runway closed","code": "R1","summary": "Runway 16/34 closed for takeoff and landing"}',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
        ]),
    ]);
    Log::shouldReceive('info')->once()->with(Mockery::on(fn ($message) => str_contains($message, "Tag Success: {$notam->id} - gpt-3.5-turbo")));

    expect($notam->status)->toBe(NotamStatus::PROCESSING);

    $tagger = new NotamOpenAiTagger();
    $tagger->tag($notam);

    $this->assertModelExists($notam);
    expect($notam->status)->toBe(NotamStatus::TAGGED);
    expect($notam->code)->toBe('R1');
    expect($notam->type)->toBe('Runway closed');
    expect($notam->summary)->toBe('Runway 16/34 closed for takeoff and landing');
    expect($notam->llm)->toBe(LLM::GPT_3_5_TURBO);
});

it('throws a custom connection exception if it cannot connect to openai', function () {
    $mock = Mockery::mock(stdClass::class);
    $mock->shouldReceive('chat')->andThrow(new TransporterException(new InvalidArgumentException()));
    app()->instance('openai', $mock);

    $notam = Notam::factory()->processing()->create();
    $tagger = new NotamOpenAiTagger();
    $tagger->tag($notam);

})->throws(TaggingConnectionException::class);

it('throws a an exception if the data from openai is not suitable for the database', function () {
    Log::shouldReceive('info')->never();
    OpenAI::fake([
        CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'role'    => 'assistant',
                        'content' => '{"key": "A0407/24-EIDW","type": "Runway closed","code": "Runway Closed","summary": "Runway 16/34 closed for takeoff and landing"}',
                    ],
                    'finish_reason' => 'stop',
                ],
            ],
        ]),
    ]);
    $notam = Notam::factory()->processing()->create();
    $tagger = new NotamOpenAiTagger();
    $tagger->tag($notam);

})
    ->throws(Error::class);
