<?php

namespace App\Enum;

enum Tag
{
    case P1;

    case P2;

    case P3;

    case P4;

    case P5;

    case P6;

    case P7;

    case P8;

    case A1;

    case A2;

    case A3;

    case A4;

    case A5;

    case R1;

    case R2;

    case R3;

    case R4;

    case R5;

    case R6;

    case T1;

    case T2;

    case T3;

    case T4;

    case T5;

    case C1;

    case C2;

    case C3;

    case C4;

    case C5;

    case C6;

    case S1;

    case S2;

    case S3;

    case S4;

    case S5;

    case S6;

    case S7;

    case H1;

    case H2;

    case H3;

    case H4;

    case H5;

    case H6;

    case H7;

    case L1;

    case L2;

    case L3;

    case L4;

    case L5;

    case L6;

    case L7;

    public function fullLabel(): string
    {
        return $this->category().' – '.$this->label();
    }

    public function category(): string
    {
        return match (substr($this->name, 0, 1)) {
            'P' => 'Airport',
            'A' => 'Approach',
            'R' => 'Runway',
            'T' => 'Taxiway',
            'C' => 'ATC',
            'S' => 'Airspace',
            'H' => 'Hazards',
            'L' => 'Library',
        };
    }

    public function label(): string
    {
        return match ($this->name) {
            'P1' => 'Airport status/hours',
            'P2' => 'Airport restriction',
            'P3' => 'Fire & Rescue',
            'P4' => 'Fuel',
            'P5' => 'Apron & Parking',
            'P6' => 'Airport Facilities',
            'P7' => 'Airport Procedure',
            'P8' => 'WIP & Construction',
            'A1' => 'Approach not available',
            'A2' => 'Approach degraded',
            'A3' => 'Approach change',
            'A4' => 'Arrival',
            'A5' => 'Departure',
            'R1' => 'Runway closed',
            'R2' => 'Runway length',
            'R3' => 'Runway strength',
            'R4' => 'Runway lights',
            'R5' => 'Runway condition',
            'R6' => 'Runway note',
            'T1' => 'Taxiway closed',
            'T2' => 'Taxiway restriction',
            'T3' => 'Taxiway lights',
            'T4' => 'Taxiway condition',
            'T5' => 'Taxiway note',
            'C1' => 'ATC status/hours',
            'C2' => 'ATC procedure',
            'C3' => 'ATC flow and delay',
            'C4' => 'Radio',
            'C5' => 'Radar & ADS',
            'C6' => 'Met',
            'S1' => 'Route closed',
            'S2' => 'Route restriction',
            'S3' => 'Route changed',
            'S4' => 'Special Use Airspace',
            'S5' => 'Special Routes',
            'S6' => 'Airspace structure',
            'S7' => 'Navaid status',
            'H1' => 'Aircraft activity',
            'H2' => 'Explosives',
            'H3' => 'Missile, Gun or Rocket Firing',
            'H4' => 'Obstacle - New',
            'H5' => 'Obstacle - Light out',
            'H6' => 'Wildlife',
            'H7' => 'GPS',
            'L1' => 'Trigger Notam',
            'L2' => 'Checklist NOTAM',
            'L3' => 'AIP Change',
            'L4' => 'AIP Chart Change',
            'L5' => 'Flight Planning',
            'L6' => 'State Rule',
            'L7' => 'Security warnings',
        };
    }

    public function description(): string
    {
        return match ($this->name) {
            'P1' => 'Airport Closed, Airport operating hours, AD AP not available',
            'P2' => 'Not available as alternate, airport slots, PPR required, max aircraft weight, etc.',
            'P3' => 'RFF Category change, Rescue equipment',
            'P4' => 'All fuel related NOTAMs, JET, JETA1, Avgas, Hydrants, Tankering',
            'P5' => 'Apron, Stands, Gates, Follow me, Apron lighting, docking, guidance, limited parking',
            'P6' => 'Equipment (GSE, Rwy/Twy equip, WDI etc.), Facilities Pax processing, airport strikes',
            'P7' => 'Manoeuvring, Handling, Deboarding, APU usage etc. NABT and noise curfew',
            'P8' => 'Work in Progress, WIP, Construction, Building works, digging, men and equipment',
            'A1' => 'Instrument approach not available, suspended eg. ILS, VOR/DME approach, RNP approach, LPV.',
            'A2' => 'Some part of ILS not working, no circling, minima not available',
            'A3' => 'Chart change, Missed approach, RVR minima',
            'A4' => 'STAR (Standard Instrument Arrival), any changes to arrival',
            'A5' => 'SID, SID not available. change, any changes to departures',
            'R1' => 'Runway Closed/Hours',
            'R2' => 'TODA, TORA, ASDA, LDA changed, Displaced THR, CWY, Width change.',
            'R3' => 'Runway PCN change, weight restriction.',
            'R4' => 'Including ALS/Approach lights, Stopbars, PAPI, VASI',
            'R5' => 'Poor surface, potholes, ungrooved, FOD, contamination (sand, ash), RWYCC',
            'R6' => 'Eg. Runway for Arrivals/Departures only, any other minor changes, runway reopened. Runway markings go here too, signs changed, missing, obscured. Building turbulence (Windshear)',
            'T1' => 'TWY closed, All taxiway closures.',
            'T2' => 'Taxiway limited to specific aircraft weight/MTOW, entry points, one-way taxiways',
            'T3' => 'TWY taxiway Lights',
            'T4' => 'Poor surface, potholes',
            'T5' => 'Taxiway Signs, markings, New named, new taxiway, re-opened.',
            'C1' => 'ATC operating hours, ATC Strike, ATC failure (ATC Zero). Including FIS/AFIS',
            'C2' => 'TWR/APP/ACC change of procedure, lost comms procedure, contingency, emergency, DCL departure clearance',
            'C3' => 'Flow control, enroute delays, expect holding',
            'C4' => 'HF, VHF, CPDLC, Satcom, ATIS - u/s, freq changes etc. KHZ, MHZ.',
            'C5' => 'Radar (PSR, MSSR, SMR, PAR, TAR), ADS (ADS-B, ADS-C) & MLAT',
            'C6' => 'Met service hours, VOLMET, Met Equipment, Met Strikes',
            'S1' => 'Airway, ATS Route closed',
            'S2' => 'Airway, ATS Route Open but some restriction',
            'S3' => 'Change to ATS Route, Airway definition',
            'S4' => "SUA's - Danger, Prohibited, Restricted, TRA",
            'S5' => 'Conditional routes. CDR open/closed. CDR1, CDR2, CDR3. Track Systems: Preferred routing, flight level allocation scheme (FLAS), User Preferred Routings (UPR), AUSOTS, Pacific OTS, NAR, NAT Tracks',
            'S6' => 'Change to specific area, eg. CTR, TMA, FIR, coordinates',
            'S7' => 'Navaids like VOR, NDB, TACAN. U/S, downgraded.',
            'H1' => 'Air Display, Aerobatics, Balloon or Kite, Exercises, Air Refuelling, Glider, Paraglider, Hang Glider, Banner towing, Mass Movement of aircraft, Parachuting (PJE), Unmanned aircraft, Formation flight, Aerial Survey, Model Flying',
            'H2' => 'Fireworks, Blasting, Demolition of explosives, Burning gas',
            'H3' => 'Military exercises involving any firing activity',
            'H4' => 'OBST Newly erected Obstacle, Crane, Wind Farm, Turbines, LIT OBST',
            'H5' => 'OBST Lights not working u/s Obstacle, Crane, Wind Farm, Turbines',
            'H6' => 'Birds, animals',
            'H7' => 'GPS outages, GPS jamming, RAIM, GNSS, EGNOS, WAAS',
            'L1' => 'Trigger Notam pointing to AIRAC change, AIC Sup, etc.',
            'L2' => 'Q-KKKK/Checklist of valid NOTAMs',
            'L3' => 'Change to AIP. Use also for AIC related NOTAM',
            'L4' => 'Small chart change ie not AIP, including enroute charts.',
            'L5' => 'Flight planning requirements, Field 18 of FPL',
            'L6' => 'National notices, Covid rules, Turkey-Greece notams',
            'L7' => 'Risk warnings, Conflict Zones, Security related NOTAM',
        };
    }

    public function color(): string
    {
        return match ($this->category()) {
            'Airport'  => 'blue',
            'Approach' => 'violet',
            'Runway'   => 'red',
            'Taxiway'  => 'yellow',
            'ATC'      => 'green',
            'Airspace' => 'fuchsia',
            'Hazards'  => 'orange',
            'Library'  => 'zinc',
        };
    }
}
