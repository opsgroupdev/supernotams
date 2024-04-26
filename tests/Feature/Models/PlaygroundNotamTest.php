<?php

use App\Models\PlaygroundNotam;

it('is deleted when the associated session is deleted', function () {
    $notam = PlaygroundNotam::factory()->create();

    $this->assertDatabaseHas(PlaygroundNotam::class, ['id' => $notam->id]);

    $notam->session->delete();

    $this->assertDatabaseMissing(PlaygroundNotam::class, ['id' => $notam->id]);
});

it('has an is_processed attribute', function () {
    expect(PlaygroundNotam::factory()->make())
        ->is_processed->toBeFalse();

    expect(PlaygroundNotam::factory()->processed()->make())
        ->is_processed->toBeFalse();
});
