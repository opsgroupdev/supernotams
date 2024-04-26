<?php

namespace App\Livewire;

use App\Livewire\Forms\PlaygroundForm;
use App\Models\PlaygroundNotam;
use App\Models\PlaygroundSession;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app', ['title' => 'Playground'])]
class Playground extends Component
{
    public PlaygroundSession $session;

    public PlaygroundForm $form;

    public function mount(PlaygroundSession|string|null $session = null): void
    {
        if (is_string($session)) {
            $session = PlaygroundSession::findOrFail($session);
        }

        $this->session = $session ?? new PlaygroundSession;
    }

    public function parse()
    {
        $this->validate();

        $this->ensureSessionCreated();

        $notam = PlaygroundNotam::make([
            'text' => $this->form->notam,
        ]);

        if ($this->session->notams()->save($notam)) {
            $notam->process();
        }

        $this->form->reset();
    }

    protected function ensureSessionCreated(): void
    {
        if ($this->session->exists) {
            return;
        }

        $this->createSession();
    }

    protected function createSession(): void
    {
        $this->session->save();

        $this->dispatch('session-created', session: $this->session->id);
    }

    public function render(): View
    {
        return view('livewire.playground');
    }
}
