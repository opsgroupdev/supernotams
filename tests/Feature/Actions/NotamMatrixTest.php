<?php

use App\Actions\NotamMatrix;

it('filters things properly', function () {
    $taggedNotams = collect([
        'EIDW' => [
            ['Text' => 'Sometext', 'TagCode' => 'B1'],
            ['Text' => 'Sometext', 'TagCode' => 'L2'],
            ['Text' => 'Sometext', 'TagCode' => 'R1'],
            ['Text' => 'Sometext', 'TagCode' => 'D3'],
            ['Text' => 'Sometext', 'TagCode' => 'A6'],
            ['Text' => 'Sometext', 'TagCode' => 'A1'],
            ['Text' => 'Sometext', 'TagCode' => 'A3'],
            ['Text' => 'Sometext', 'TagCode' => 'A2'],
            ['Text' => 'Sometext', 'TagCode' => 'R4'],
            ['Text' => 'Sometext', 'TagCode' => 'R2'],
            ['Text' => 'Sometext', 'TagCode' => 'L1'],
        ],
        'EGLL' => [
            ['Text' => 'Sometext', 'TagCode' => 'A2'],
            ['Text' => 'Sometext', 'TagCode' => 'A4'],
            ['Text' => 'Sometext', 'TagCode' => 'R1'],
            ['Text' => 'Sometext', 'TagCode' => 'R3'],
            ['Text' => 'Sometext', 'TagCode' => 'R2'],
            ['Text' => 'Sometext', 'TagCode' => 'A5'],
            ['Text' => 'Sometext', 'TagCode' => 'L2'],
            ['Text' => 'Sometext', 'TagCode' => 'H1'],
            ['Text' => 'Sometext', 'TagCode' => 'H5'],
        ],
    ]);

    $airportsAndFirs = [
        'departureAirport' => 'EIDW',
        'destinationAirport' => 'EGLL',
    ];

    $results = NotamMatrix::process($airportsAndFirs, $taggedNotams);

    expect($results['primary'])->toMatchArray(
        [
            'departureAirport' => [
                'EIDW' => [
                    ['Text' => 'Sometext', 'TagCode' => 'A1'],
                    ['Text' => 'Sometext', 'TagCode' => 'A2'],
                    ['Text' => 'Sometext', 'TagCode' => 'A3'],
                    ['Text' => 'Sometext', 'TagCode' => 'R1'],
                    ['Text' => 'Sometext', 'TagCode' => 'R2'],
                    ['Text' => 'Sometext', 'TagCode' => 'R4'],
                ],
            ],
            'destinationAirport' => [
                'EGLL' => [
                    ['Text' => 'Sometext', 'TagCode' => 'A2'],
                    ['Text' => 'Sometext', 'TagCode' => 'A4'],
                    ['Text' => 'Sometext', 'TagCode' => 'A5'],
                    ['Text' => 'Sometext', 'TagCode' => 'R1'],
                    ['Text' => 'Sometext', 'TagCode' => 'R2'],
                    ['Text' => 'Sometext', 'TagCode' => 'R3'],
                ],
            ],
        ]
    );

    expect($results['appendix'])->toMatchArray(
        [
            'departureAirport' => [
                'EIDW' => [
                    ['Text' => 'Sometext', 'TagCode' => 'B1'],
                    ['Text' => 'Sometext', 'TagCode' => 'L2'],
                    ['Text' => 'Sometext', 'TagCode' => 'D3'],
                    ['Text' => 'Sometext', 'TagCode' => 'A6'],
                    ['Text' => 'Sometext', 'TagCode' => 'L1'],
                ],
            ],
            'destinationAirport' => [
                'EGLL' => [
                    ['Text' => 'Sometext', 'TagCode' => 'L2'],
                    ['Text' => 'Sometext', 'TagCode' => 'H1'],
                    ['Text' => 'Sometext', 'TagCode' => 'H5'],
                ],
            ],
        ]
    );

    //expect($results->flatten())->toHaveCount(20);
});
