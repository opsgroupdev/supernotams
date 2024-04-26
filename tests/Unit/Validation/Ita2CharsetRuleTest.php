<?php

use App\Rules\Ita2CharsetRule;

it('passes with characters from the ITA2 charset', function (string $character) {
    $rule = new Ita2CharsetRule;

    expect($rule)->toPassWith($character);
})->with(str_split(Ita2CharsetRule::ITA2_CHARACTERS));

it('fails with characters outside the ITA2 charset', function (string $character) {
    $rule = new Ita2CharsetRule;

    expect($rule)->toFailWith($character);
})->with(str_split('abcdefghijklmnopqrstuvwxyz@#$%^*~'));
