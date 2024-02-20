<?php

use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use Illuminate\Support\Collection;

function chunkArray(Collection $collection)
{
    $tok = new Gpt3Tokenizer(new Gpt3TokenizerConfig());

    $sortedGroup = $collection->sortByDesc(fn ($item) => $tok->count($item['message']))->values();
    $chunks = [];

    foreach ($sortedGroup as $notam) {
        $added = false;

        // Find the chunk with the closest sum to 10
        foreach ($chunks as &$chunk) {
            $sum = collect($chunk)
                ->map(fn ($item) => $tok->count(
                    collect($item)->only(['id', 'content'])->toJson()
                ) + 90)
                ->sum();

            // If adding the value to the chunk keeps the sum <= 10, add it
            if ($sum + $tok->count(collect($notam)->only(['id', 'content'])->toJson()) <= 4000) {
                $chunk[] = $notam;
                $added = true;
                break;
            }
        }

        // If no existing chunk can accommodate the value, create a new chunk
        if (! $added) {
            $chunks[] = [$notam];
        }
    }

    return $chunks;
}

it('generates a pdf file from the filtered notams', function () {
    //    $filteredNotams = collect(json_decode(file_get_contents(base_path('tests/source/ICAO_Filtered_NotamsWithTags.json')), true));
    //
    //    \App\Actions\PDFCreator::create($filteredNotams);

    $notams = collect(json_decode(file_get_contents(base_path('tests/source/ICAO_Notam_Source.json')), true));

    $chunks = chunkArray($notams);

    dd($chunks);
});
