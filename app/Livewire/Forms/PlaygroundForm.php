<?php

namespace App\Livewire\Forms;

use App\Rules\Ita2CharsetRule;
use Livewire\Form;

class PlaygroundForm extends Form
{
    public string $notam = '';

    public function rules(): array
    {
        return [
            'notam' => [
                'required',
                'min:5',
                new Ita2CharsetRule,
            ],
        ];
    }
}
