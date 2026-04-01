<?php

namespace App\Livewire\Dashboard\Contacts;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public Contact|Collection $contact;

    public string|null $date;

    public function mount()
    {
        $this->authorize('viewAny', Contact::class);
    }

    #[On('show-result')]
    public function getResult($id)
    {
        $this->contact = Contact::findOrFail($id);

        $this->date = $this->contact?->created_at->isoFormat(config('app.time_format'));
    }

    public function render()
    {
        return view('livewire.dashboard.contacts.show');
    }
}
