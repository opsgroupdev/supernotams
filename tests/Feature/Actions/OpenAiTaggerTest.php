<?php

use App\Actions\OpenAITagger;
use App\OpenAI\Prompt;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use OpenAI\Responses\Chat\CreateResponse;

it('sends the notams in batches and gets tags', function () {
    Event::fake();
    //Use our baseline 60 known notams for EIDW and EGLL
    $notams = collect(json_decode(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')), true));

    //Set up a fake OpenAi which simulates the response we would get if we actually sent those notams to openai
    $source = json_decode(file_get_contents(base_path('tests/source/OpenAI_Responses.json')), true);

    \OpenAI\Laravel\Facades\OpenAI::fake(
        [
            CreateResponse::fake($source[0]),
            CreateResponse::fake($source[1]),
            CreateResponse::fake($source[2]),
            CreateResponse::fake($source[3]),
            CreateResponse::fake($source[4]),
        ]
    );

    //Tag the notams!
    $results = OpenAITagger::tag($notams);

    expect($results)->toHaveCount(2); //Number of airports
    expect($results)->toHaveKeys(['EIDW', 'EGLL']);
    expect($results->collapse())->toHaveCount(60);
    expect($results->collapse())->each()->toHaveKeys(['TagName', 'TagCode', 'Explanation']);
    expect($results['EIDW'][0]['TagName'])->toBe('Departure');
    expect($results['EIDW'][0]['TagCode'])->toBe('A5');
    expect($results['EIDW'][0]['Explanation'])->toBe('RNAV SID RWY 28R changed');
});

it('tests parallel requests', function () {
    //    Http::fake(['*' => Http::response('Hello World', 200, ['Headers']),]);
    $notams = collect([
        "TWY E1 CLOSED\nREF AIP SUPPLEMENT 021/2022.\nCREATED: 12 May 2023 20:08:00 \nSOURCE: EUECYIYN",
        "IMPLEMENTING RWY 10R/28L TEMPORARY DISPLACED THRESHOLD.\nRWY 10R:\nTORA 2141M\nTODA 2201M\nASDA 2141M\nLDA 2141M\nRWY 28L:\nTORA 2141M\nTODA 2354M\nASDA 2197M\nLDA 2141M\nEXPECT DELAYS OF 20 MINUTES.\nREF AIP SUPPLEMENT 021/2022\nCREATED: 12 May 2023 20:07:00 \nSOURCE: EUECYIYN",
        "RWY 28L VOR-T IAP NOT AVAILABLE \nREF AIP EIDW AD 2.24-45\nCREATED: 12 May 2023 16:54:00 \nSOURCE: EUECYIYN",
    ]);

    $results = Http::asJson()
        ->timeout(100)
        ->connectTimeout(100)
        ->withHeaders(['Host' => 'api.openai.com'])
        ->pool(function (Illuminate\Http\Client\Pool $pool) use ($notams) {
            foreach ($notams as $text) {
                $pool
                    ->connectTimeout(100)
                    ->timeout(100)
                    ->withToken(config('openai.api_key'))
                    ->post('https://api.openai.com/v1/chat/completions',
                        [
                            'model' => 'gpt-3.5-turbo',
                            'temperature' => 0,
                            'messages' => array_merge(Prompt::get(), [['role' => 'user', 'content' => $text]]),
                        ]);
            }
        });

    $final = collect($results)->map(function ($response) {
        $result = $response->json();

        return json_decode($result['choices'][0]['message']['content'], true);
    });

    //    $recorded = Http::recorded();
    //    $answer = json_decode($response['choices'][0]['message']['content'], true);
});
