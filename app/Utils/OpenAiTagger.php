<?php

namespace App\Utils;

use App\Events\NotamProcessingEvent;
use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAiTagger
{

    protected Collection $chunkedNotams;
    protected Gpt3Tokenizer $tokenizer;

    public function __construct(protected Collection $notams, protected ?string $channelName = null)
    {
        $this->chunkedNotams = collect();
    }

    public static function tag(Collection $notams, ?string $channelName = null): Collection
    {
        return (new self($notams, $channelName))->get();
    }

    protected function get(): Collection
    {
        $this->optimallyGroupNotams();

        $taggedData = $this->tagAllNotams();

        return $this->notams
            ->map(fn (array $airportNotams) => $this->mergeTaggedData($airportNotams, $taggedData));
    }

    private function optimallyGroupNotams(): void
    {
        $totalTokens = 0;
        $promptTokens = $this->getTokenizer()->count(collect(self::prompt())->pluck('content')->implode(' '));
        $maxTokensPerRequest = 4000 - $promptTokens;
        $currentGroup = collect();

        $this->notams
            ->collapse()
            ->each(function (array $notam) use (&$currentGroup, &$totalTokens, $maxTokensPerRequest) {
                $notamTokenCount = $this->getTokenizer()->count("{$notam['key']}': {$notam['text']}") + 90; //Add estimate reply token here

                //Adding this notam will push us over our maximum token allowance for this group.
                //Push the group onto the array. It is full.
                if ($totalTokens + $notamTokenCount > $maxTokensPerRequest) {
                    $this->chunkedNotams->push($currentGroup);
                    $currentGroup = collect();
                    $totalTokens = 0;
                }

                //Still more room in this group.
                $currentGroup->push($notam);
                $totalTokens += $notamTokenCount;
            });

        // Add the last group if it contains any strings
        if ($currentGroup->isNotEmpty()) {
            $this->chunkedNotams->push($currentGroup);
        }
    }

    protected function getTokenizer(): Gpt3Tokenizer
    {
        return $this->tokenizer ??= new Gpt3Tokenizer(new Gpt3TokenizerConfig());
    }

    public static function prompt(): array
    {
        return [
            [
                'role'    => 'user',
                'content' => "An json array of NOTAM Tags, each tag has three columns: 'Tag Code', 'Tag Name', 'Tag Description': \n" . json_encode(self::tags()),
            ],
            [
                'role'    => 'user',
                'content' => "You are a NOTAM Librarian. I will give you a number of NOTAM messages. Each start with an identity key then a colon. Create a JSON array object with the following 4 fields per notam:\n'key': The notam identity key.\n'TagName': Choose the most logical Tag for this NOTAM from the list of Tags.\n'TagCode': The code for the selected Tag Name.\n'Explanation': In very simple English only, explain the NOTAM in a maximum of seven words, use sentence case, but do not use abbreviations.",
            ],
        ];
    }

    protected static function tags(): Collection
    {
        return collect([
            ["P1", "Airport status/hours", "Airport Closed, Airport operating hours, AD AP not available"],
            [
                "P2",
                "Airport restriction",
                "Not available as alternate, airport slots, PPR required, max aircraft weight, etc.",
            ],
            ["P3", "Fire & Rescue", "RFF Category change, Rescue equipment"],
            ["P4", "Fuel", "All fuel related NOTAMs, JET, JETA1, Avgas, Hydrants, Tankering"],
            [
                "P5",
                "Apron & Parking",
                "Apron, Stands, Gates, Follow me, Apron lighting, docking, guidance, limited parking",
            ],
            [
                "P6",
                "Airport Facilities",
                "Equipment (GSE, Rwy/Twy equip, WDI etc.), Facilities Pax processing, airport strikes",
            ],
            ["P7", "Airport Procedure", "Manoeuvring, Handling, Deboarding, APU usage etc. NABT and noise curfew"],
            [
                "P8",
                "WIP & Construction",
                "Work in Progress, WIP, Construction, Building works, digging, men and equipment",
            ],
            [
                "A1",
                "Approach not available",
                "Instrument approach not available, suspended eg. ILS, VOR/DME approach, RNP approach, LPV.",
            ],
            ["A2", "Approach degraded", "Some part of ILS not working, no circling, minima not available"],
            ["A3", "Approach change", "Chart change, Missed approach, RVR minima"],
            ["R1", "Runway closed", "Runway Closed/Hours"],
            ["R2", "Runway length", "TODA, TORA, ASDA, LDA changed, Displaced THR, CWY, Width change."],
            ["R3", "Runway strength", "Runway PCN change, weight restriction."],
            ["R4", "Runway lights", "Including ALS/Approach lights, Stopbars, PAPI, VASI"],
            ["R5", "Runway condition", "Poor surface, potholes, ungrooved, FOD, contamination (sand, ash), RWYCC"],
            [
                "R6",
                "Runway note",
                "Eg. Runway for Arrivals/Departures only, any other minor changes, runway reopened. Runway markings go here too, signs changed, missing, obscured. Building turbulence (Windshear)",
            ],
            ["T1", "Taxiway closed", "TWY closed, All taxiway closures."],
            [
                "T2",
                "Taxiway restriction",
                "Taxiway limited to specific aircraft weight/MTOW, entry points, one-way taxiways",
            ],
            ["T3", "Taxiway lights", "TWY taxiway Lights"],
            ["T4", "Taxiway condition", "Poor surface, potholes"],
            ["T5", "Taxiway note", "Taxiway Signs, markings, New named, new taxiway, re-opened."],
            ["C1", "ATC status/hours", "ATC operating hours, ATC Strike, ATC failure (ATC Zero). Including FIS/AFIS"],
            [
                "C2",
                "ATC procedure",
                "TWR/APP/ACC change of procedure, lost comms procedure, contingency, emergency, DCL departure clearance",
            ],
            ["C3", "ATC flow and delay", "Flow control, enroute delays, expect holding"],
            ["C4", "Radio", "HF, VHF, CPDLC, Satcom, ATIS - u/s, freq changes etc. KHZ, MHZ."],
            ["C5", "Radar & ADS", "Radar (PSR, MSSR, SMR, PAR, TAR), ADS (ADS-B, ADS-C) & MLAT"],
            ["C6", "Met", "Met service hours, VOLMET, Met Equipment, Met Strikes"],
            ["N1", "Navaid status", "Navaids like VOR, NDB, TACAN. U/S, downgraded."],
            ["N2", "Arrival", "STAR (Standard Instrument Arrival), any changes to arrival"],
            ["N3", "Departure", "SID, SID not available. change, any changes to departures"],
            ["N4", "GPS", "GPS outages, GPS jamming, RAIM, GNSS, EGNOS, WAAS"],
            ["S1", "Route closed", "Airway, ATS Route closed"],
            ["S2", "Route restriction", "Airway, ATS Route Open but some restriction"],
            ["S3", "Route changed", "Change to ATS Route, Airway definition"],
            ["S4", "Special Use Airspace", "SUA's - Danger, Prohibited, Restricted, TRA"],
            [
                "S5",
                "Special Routes",
                "Conditional routes. CDR open/closed. CDR1, CDR2, CDR3. Track Systems: Preferred routing, flight level allocation scheme (FLAS), User Preferred Routings (UPR), AUSOTS, Pacific OTS, NAR, NAT Tracks",
            ],
            ["S6", "Airspace structure", "Change to specific area, eg. CTR, TMA, FIR, coordinates"],
            [
                "H1",
                "Aircraft activity",
                "Air Display, Aerobatics, Balloon or Kite, Exercises, Air Refuelling, Glider, Paraglider, Hang Glider, Banner towing, Mass Movement of aircraft, Parachuting (PJE), Unmanned aircraft, Formation flight, Aerial Survey, Model Flying",
            ],
            ["H2", "Explosives", "Fireworks, Blasting, Demolition of explosives, Burning gas"],
            ["H3", "Missile, Gun or Rocket Firing", "Military exercises involving any firing activity"],
            ["H4", "Obstacle - New", "OBST Newly erected Obstacle, Crane, Wind Farm, Turbines, LIT OBST"],
            ["H5", "Obstacle - Light out", "OBST Lights not working u/s Obstacle, Crane, Wind Farm, Turbines"],
            ["H6", "Wildlife", "Birds, animals"],
            ["L1", "Trigger Notam", "Trigger Notam pointing to AIRAC change, AIC Sup, etc."],
            ["L2", "Checklist NOTAM", "Q-KKKK/Checklist of valid NOTAMs"],
            ["L3", "AIP Change", "Change to AIP. Use also for AIC related NOTAM"],
            ["L4", "AIP Chart Change", "Small chart change ie not AIP, including enroute charts."],
            ["L5", "Flight Planning", "Flight planning requirements, Field 18 of FPL"],
            ["L6", "State Rule", "National notices, Covid rules, Turkey-Greece notams"],
            ["L7", "Security warnings", "Risk warnings, Conflict Zones, Security related NOTAM"],
        ]);
    }

    protected function tagAllNotams(): Collection
    {
        return $this->chunkedNotams
            ->map(fn (Collection $notamChunk) => $this->rateLimitTaggingRequestFor($notamChunk))
            ->collapse();
    }

    protected function rateLimitTaggingRequestFor($chunk)
    {
        if (RateLimiter::tooManyAttempts('open_ai_api_request', $perMinute = 60)) {
            $seconds = RateLimiter::availableIn('open_ai_api_request');
            sleep($seconds + 3);
        }

        return RateLimiter::attempt(
            'open_ai_api_request',
            60, //number of attempts
            fn () => $this->tagChunk($chunk),
            60 //every xx seconds
        );
    }

    protected function tagChunk(Collection $notams): array
    {
        $content = $notams->map(fn (array $notam) => "{$notam['key']}: {$notam['text']}")->implode("\n\n\n\n");

        $response = OpenAI::chat()->create([
            'model'       => 'gpt-3.5-turbo',
            'temperature' => 0,
            'messages'    => array_merge(self::prompt(), [['role' => 'user', 'content' => $content]]),
        ]);

        if ($this->channelName) {
            event(new NotamProcessingEvent(
                $this->channelName,
                sprintf("Phewf! - We've just processed a batch of %s Notams!", count($notams))
                ));
        }

        return json_decode($response->choices[0]->message->content, true);
    }

    protected function mergeTaggedData(array $airportNotams, Collection $taggedData): array
    {
        return collect($airportNotams)
            ->map(fn ($notam) => $this->mergeData($taggedData, $notam))
            ->toArray();
    }

    protected function mergeData(Collection $taggedData, $notam): array
    {
        return collect($notam)
            ->union($taggedData->firstWhere('key', $notam['key']))
            ->toArray();
    }
}