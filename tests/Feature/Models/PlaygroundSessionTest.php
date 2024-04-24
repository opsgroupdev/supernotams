<?php

use App\Models\PlaygroundNotam;
use App\Models\PlaygroundSession;
use Illuminate\Support\Facades\Artisan;

it('prunes empty sessions older than one hour', function () {
    $session = PlaygroundSession::factory()->create();

    Artisan::call('model:prune');

    $this->assertDatabaseHas(PlaygroundSession::class, ['id' => $session->id]);

    $this->travel(61)->minutes();

    Artisan::call('model:prune');

    $this->assertDatabaseMissing(PlaygroundSession::class, ['id' => $session->id]);
});

it('prunes sessions untouched for over a month', function () {
    $session = PlaygroundSession::factory()
        ->has(PlaygroundNotam::factory(), 'notams')
        ->create();

    Artisan::call('model:prune');

    $this->assertDatabaseHas(PlaygroundSession::class, ['id' => $session->id]);

    $this->travel(32)->days();

    Artisan::call('model:prune');

    $this->assertDatabaseMissing(PlaygroundSession::class, ['id' => $session->id]);
});

it('receives an IP on creation', function () {
    $session = PlaygroundSession::factory()->make(['ip_address' => null]);

    expect($session->ip_address)->toBeNull();

    $session->save();

    expect($session->ip_address)->not->toBeNull();
});
