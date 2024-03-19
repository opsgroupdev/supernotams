<?php

namespace App\Http\Controllers;

use App\Jobs\NotamProcessingJob;
use App\Rules\IsAtcFlightPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class NotamController extends Controller
{
    public function index()
    {
        return inertia('Notams/NotamsIndex', [
            'session_id' => Session::getId(),
        ]);
    }

    public function store(Request $request)
    {
        \Log::info('About to validate input');
        $validated = $request->validate([
            'flight_plan' => ['required', 'string', new IsAtcFlightPlan],
        ]);

        \Log::info('About to dispatch Processing job');
        NotamProcessingJob::dispatch($validated['flight_plan'], Session::getId());
        \Log::info('Dispatched Processing job');
        //        return redirect()->route('notam.index');
    }

    public function show($cacheKey)
    {
        $pdf = Cache::get($cacheKey);

        return $pdf ? Response::make($pdf, 200,
            [
                'Content-type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename=notampack.pdf',
            ])
            : response('Sorry link has expired');
    }
}
