<?php

namespace App\Actions;

use App\Models\Notam;
use App\OpenAI\Matrix;
use Illuminate\Support\Collection;

class NotamFilter
{
    protected array $primaryNotams = [];

    protected array $appendixNotams = [];

    /** @var Collection<string, Collection> */
    protected Collection $taggedNotams;

    public function filter($icaoLocators, Collection $taggedNotams): Collection
    {
        //Ensure each new pass has a reset state.
        $this->primaryNotams = [];
        $this->appendixNotams = [];
        $this->taggedNotams = $taggedNotams->groupBy(fn (Notam $notam) => substr($notam->id, -4));

        foreach ($icaoLocators as $locationType => $icaoCodes) {
            foreach ($icaoCodes as $icaoCode) {
                if ($this->icaoCodeHasNotams($locationType, $icaoCode)) {
                    $this->organiseNotams($locationType, $icaoCode);
                }
            }
        }

        return collect(['primary' => $this->primaryNotams, 'appendix' => $this->appendixNotams]);
    }

    protected function icaoCodeHasNotams(string $locationType, string $icaoCode): bool
    {
        return Matrix::has($locationType) && $this->taggedNotams->has($icaoCode);
    }

    protected function organiseNotams(string $locationType, string $icaoCode): void
    {
        //Let's get the relevant codes for the type of locator provided. (Airport/FIR etc.)
        $applicableCodes = Matrix::get($locationType);

        //Get all previously tagged notams for this locator
        $notamsForIcaoLocation = $this->taggedNotams->get($icaoCode);

        //Filter this list for notams that actually match one of the codes we're interested in.
        $filteredNotams = $notamsForIcaoLocation->filter(fn ($notam) => $applicableCodes->contains($notam['code']));

        //Rearrange the notams based on the order specified in the matrix
        $this->primaryNotams[$locationType][$icaoCode] = $filteredNotams
            ->sortBy(fn ($notam) => $applicableCodes->search($notam['code']))->values();

        //Now let's add ALL left over notams not used in the Primary section to the Appendix section.
        $this->appendixNotams[$locationType][$icaoCode] = $notamsForIcaoLocation
            ->diffKeys($filteredNotams)
            ->values();
    }
}
