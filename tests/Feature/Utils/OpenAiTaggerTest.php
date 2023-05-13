<?php

use App\Utils\OpenAiTagger;
use OpenAI\Responses\Chat\CreateResponse;

it('sends the notams in batches and gets tags', function () {
    $source = json_decode(file_get_contents(base_path('tests/source/alltags.json')), true);

    \OpenAI\Laravel\Facades\OpenAI::fake(
        [
            CreateResponse::fake(['choices' => [['message' => ['content' => $source[0]]]]]),
            CreateResponse::fake(['choices' => [['message' => ['content' => $source[1]]]]]),
            CreateResponse::fake(['choices' => [['message' => ['content' => $source[2]]]]]),
            CreateResponse::fake(['choices' => [['message' => ['content' => $source[3]]]]]),
            CreateResponse::fake(['choices' => [['message' => ['content' => $source[4]]]]]),
            CreateResponse::fake(['choices' => [['message' => ['content' => $source[5]]]]]),
        ]
    );
    $notams = collect(json_decode(file_get_contents(base_path('tests/source/allnotams.json')), true));

    $results = OpenAiTagger::tag($notams);
//file_put_contents(base_path('tests/source/alltagged.json'), json_encode($results));
    expect($results)->toHaveCount(2); //Number of airports
    expect($results->collapse())->toHaveCount(60); //Number of airports
    expect($results->collapse())->each()->toHaveKeys(['TagName', 'TagCode', 'Explanation']);
    expect($results['EIDW'][0]['TagName'])->toBe('Taxiway closed');
    expect($results['EIDW'][0]['TagCode'])->toBe('T1');
    expect($results['EIDW'][0]['Explanation'])->toBe('TWY P1 AND F-OUTER CLSD');
});