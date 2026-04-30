<?php

namespace App\Livewire\Dashboard\Questions;

use App\Models\Question;
use Livewire\Component;

class Create extends Component
{
    public string $question = '';

    public string $answer = '';

    public function mount()
    {
        $this->authorize('manage settings');
    }

    public function store()
    {
        $this->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:1000'],
        ]);

        Question::create([
            'title' => $this->question,
            'answer' => $this->answer,
        ]);

        $this->dispatch('hideModal', ['id' => 'createQuestionModal']);

        $this->dispatch('refreshTable');

        $this->reset();
        $this->mount();
    }

    public function render()
    {
        return view('livewire.dashboard.questions.create');
    }
}
