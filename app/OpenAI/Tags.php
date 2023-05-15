<?php

namespace App\OpenAI;

use Illuminate\Support\Collection;

class Tags
{
    public static function all(): Collection
    {
        // @formatter:off
        return collect([
            ['P1', 'Airport status/hours', 'Airport Closed, Airport operating hours, AD AP not available'],
            ['P2', 'Airport restriction', 'Not available as alternate, airport slots, PPR required, max aircraft weight, etc.'],
            ['P3', 'Fire & Rescue', 'RFF Category change, Rescue equipment'],
            ['P4', 'Fuel', 'All fuel related NOTAMs, JET, JETA1, Avgas, Hydrants, Tankering'],
            ['P5', 'Apron & Parking', 'Apron, Stands, Gates, Follow me, Apron lighting, docking, guidance, limited parking'],
            ['P6', 'Airport Facilities', 'Equipment (GSE, Rwy/Twy equip, WDI etc.), Facilities Pax processing, airport strikes'],
            ['P7', 'Airport Procedure', 'Manoeuvring, Handling, Deboarding, APU usage etc. NABT and noise curfew'],
            ['P8', 'WIP & Construction', 'Work in Progress, WIP, Construction, Building works, digging, men and equipment'],
            ['A1', 'Approach not available', 'Instrument approach not available, suspended eg. ILS, VOR/DME approach, RNP approach, LPV.'],
            ['A2', 'Approach degraded', 'Some part of ILS not working, no circling, minima not available'],
            ['A3', 'Approach change', 'Chart change, Missed approach, RVR minima'],
            ['A4', 'Arrival', 'STAR (Standard Instrument Arrival), any changes to arrival'],
            ['A5', 'Departure', 'SID, SID not available. change, any changes to departures'],
            ['R1', 'Runway closed', 'Runway Closed/Hours'],
            ['R2', 'Runway length', 'TODA, TORA, ASDA, LDA changed, Displaced THR, CWY, Width change.'],
            ['R3', 'Runway strength', 'Runway PCN change, weight restriction.'],
            ['R4', 'Runway lights', 'Including ALS/Approach lights, Stopbars, PAPI, VASI'],
            ['R5', 'Runway condition', 'Poor surface, potholes, ungrooved, FOD, contamination (sand, ash), RWYCC'],
            ['R6', 'Runway note', 'Eg. Runway for Arrivals/Departures only, any other minor changes, runway reopened. Runway markings go here too, signs changed, missing, obscured. Building turbulence (Windshear)'],
            ['T1', 'Taxiway closed', 'TWY closed, All taxiway closures.'],
            ['T2', 'Taxiway restriction', 'Taxiway limited to specific aircraft weight/MTOW, entry points, one-way taxiways'],
            ['T3', 'Taxiway lights', 'TWY taxiway Lights'],
            ['T4', 'Taxiway condition', 'Poor surface, potholes'],
            ['T5', 'Taxiway note', 'Taxiway Signs, markings, New named, new taxiway, re-opened.'],
            ['C1', 'ATC status/hours', 'ATC operating hours, ATC Strike, ATC failure (ATC Zero). Including FIS/AFIS'],
            ['C2', 'ATC procedure', 'TWR/APP/ACC change of procedure, lost comms procedure, contingency, emergency, DCL departure clearance'],
            ['C3', 'ATC flow and delay', 'Flow control, enroute delays, expect holding'],
            ['C4', 'Radio', 'HF, VHF, CPDLC, Satcom, ATIS - u/s, freq changes etc. KHZ, MHZ.'],
            ['C5', 'Radar & ADS', 'Radar (PSR, MSSR, SMR, PAR, TAR), ADS (ADS-B, ADS-C) & MLAT'],
            ['C6', 'Met', 'Met service hours, VOLMET, Met Equipment, Met Strikes'],
            ['S1', 'Route closed', 'Airway, ATS Route closed'],
            ['S2', 'Route restriction', 'Airway, ATS Route Open but some restriction'],
            ['S3', 'Route changed', 'Change to ATS Route, Airway definition'],
            ['S4', 'Special Use Airspace', "SUA's - Danger, Prohibited, Restricted, TRA"],
            ['S5', 'Special Routes', 'Conditional routes. CDR open/closed. CDR1, CDR2, CDR3. Track Systems: Preferred routing, flight level allocation scheme (FLAS), User Preferred Routings (UPR), AUSOTS, Pacific OTS, NAR, NAT Tracks'],
            ['S6', 'Airspace structure', 'Change to specific area, eg. CTR, TMA, FIR, coordinates'],
            ['S7', 'Navaid status', 'Navaids like VOR, NDB, TACAN. U/S, downgraded.'],
            ['H1', 'Aircraft activity', 'Air Display, Aerobatics, Balloon or Kite, Exercises, Air Refuelling, Glider, Paraglider, Hang Glider, Banner towing, Mass Movement of aircraft, Parachuting (PJE), Unmanned aircraft, Formation flight, Aerial Survey, Model Flying'],
            ['H2', 'Explosives', 'Fireworks, Blasting, Demolition of explosives, Burning gas'],
            ['H3', 'Missile, Gun or Rocket Firing', 'Military exercises involving any firing activity'],
            ['H4', 'Obstacle - New', 'OBST Newly erected Obstacle, Crane, Wind Farm, Turbines, LIT OBST'],
            ['H5', 'Obstacle - Light out', 'OBST Lights not working u/s Obstacle, Crane, Wind Farm, Turbines'],
            ['H6', 'Wildlife', 'Birds, animals'],
            ['H7', 'GPS', 'GPS outages, GPS jamming, RAIM, GNSS, EGNOS, WAAS'],
            ['L1', 'Trigger Notam', 'Trigger Notam pointing to AIRAC change, AIC Sup, etc.'],
            ['L2', 'Checklist NOTAM', 'Q-KKKK/Checklist of valid NOTAMs'],
            ['L3', 'AIP Change', 'Change to AIP. Use also for AIC related NOTAM'],
            ['L4', 'AIP Chart Change', 'Small chart change ie not AIP, including enroute charts.'],
            ['L5', 'Flight Planning', 'Flight planning requirements, Field 18 of FPL'],
            ['L6', 'State Rule', 'National notices, Covid rules, Turkey-Greece notams'],
            ['L7', 'Security warnings', 'Risk warnings, Conflict Zones, Security related NOTAM'],
        ]);
    }

    public static function asJson(): string
    {
        return json_encode(self::all());
    }
}
