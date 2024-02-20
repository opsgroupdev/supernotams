<?php

namespace App\Actions;

use Barryvdh\Snappy\PdfWrapper;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class PDFCreator
{
    public static function create(Collection $filteredNotams): ?string
    {
        return (new self())->generate($filteredNotams);
    }

    public function generate(Collection $filteredNotams): ?string
    {
        try {
            /** @var PdfWrapper $pdf */
            $pdf = App::make('snappy.pdf.wrapper');

            $pdfString = $pdf->loadView(
                'pdf.notams_briefing_pack',
                ['filteredNotams' => $filteredNotams]
            )
                ->setPaper('a4')
                ->setOrientation('portrait')
                ->setOptions([
                    'margin-top'    => '4mm',
                    'margin-bottom' => '4mm',
                    'margin-left'   => '3mm',
                    'margin-right'  => '3mm',
                ])
                ->output();

            Cache::put($key = md5($pdfString), $pdfString, now()->addMinutes(30));

            return $key;
        } catch (Exception $exception) {
            report($exception);
        }

        return null;
    }
}
