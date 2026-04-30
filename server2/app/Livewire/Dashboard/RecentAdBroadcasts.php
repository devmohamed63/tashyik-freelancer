<?php

namespace App\Livewire\Dashboard;

use App\Models\AdBroadcast;
use App\Models\Banner;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class RecentAdBroadcasts extends Component
{
    /**
     * On the dedicated push-ads page, resend fills the composer inline (no modal).
     */
    public bool $useModalForResend = true;

    /**
     * When true (list-only page), «Resend» sends the user to the compose page with ?resend=.
     */
    public bool $redirectOnResend = false;

    public function mount(): void
    {
        $this->authorize('viewAny', Banner::class);
    }

    public function promptResend(int $id): void
    {
        Gate::authorize('create', Banner::class);

        if ($this->redirectOnResend) {
            $this->redirect(route('dashboard.push-ads.create', ['resend' => $id]));

            return;
        }

        $this->dispatch('fillCreateAdFromBroadcast', id: $id);

        if ($this->useModalForResend) {
            $this->dispatch('showModal', ['id' => 'createAdModal']);
        }
    }

    #[On('refreshTable')]
    public function render(): View
    {
        $broadcasts = AdBroadcast::query()
            ->latest()
            ->limit(25)
            ->get();

        return view('livewire.dashboard.recent-ad-broadcasts', [
            'broadcasts' => $broadcasts,
        ]);
    }
}
