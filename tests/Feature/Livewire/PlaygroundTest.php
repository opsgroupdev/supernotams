<?php

use App\Livewire\Playground;
use App\Models\PlaygroundNotam;
use App\Models\PlaygroundSession;

use function Pest\Laravel\get;

it('loads properly without a session', function () {
    get(route('playground'))
        ->assertOk()
        ->assertSeeLivewire(Playground::class);
});

it('loads properly with a session', function () {
    $session = PlaygroundSession::factory()->create();

    get(route('playground', $session))
        ->assertOk()
        ->assertSeeLivewire(Playground::class);

    Livewire::test(Playground::class, ['session' => $session])
        ->assertSet('session', $session);
});

it('show an empty state when no NOTAMs have been parsed in the session', function () {
    Livewire::test(Playground::class)
        ->assertSeeText('Get started playing with SuperNOTAMs');
});

it('shows the history of parsed NOTAMs in the session', function () {
    $session = PlaygroundSession::factory()
        ->has(PlaygroundNotam::factory()->processed(), 'notams')
        ->create();

    $notam = $session->notams->first();

    Livewire::test(Playground::class, ['session' => $session])
        ->assertSeeText($notam->text)
        ->assertSeeText($notam->tag->fullLabel())
        ->assertSeeText($notam->summary);
});

it('creates a new session when a NOTAM is parsed', function () {
    $livewire = Livewire::test(Playground::class);

    expect($livewire->get('session'))
        ->exists->toBeFalse();

    $livewire->set('form.notam', 'THIS IS A TEST NOTAM')
        ->call('parse');

    expect($livewire->get('session'))
        ->toBeInstanceOf(PlaygroundSession::class)
        ->exists->toBeTrue();
});

it('validates user input', function (string $rule, mixed $input) {
    Livewire::test(Playground::class)
        ->set('form.notam', $input)
        ->call('parse')
        ->assertHasErrors(['form.notam' => $rule]);
})->with([
    ['required', ''],
    ['min:5', 'FOO'],
]);

it('parses a NOTAM and displays the AI generated result', function () {
    $livewire = Livewire::test(Playground::class);

    $livewire
        ->set('form.notam', 'ILS RWY 25 U/S DUE MAINTENANCE')
        ->call('parse')
        ->assertHasNoErrors();

    $notam = $livewire->get('session')->notams->first();

    expect($notam)
        ->toBeInstanceOf(PlaygroundNotam::class)
        ->is_processed->toBeTrue();

    $livewire
        ->assertSeeText($notam->text)
        ->assertSeeText($notam->tag->fullLabel())
        ->assertSeeText($notam->summary);
});
