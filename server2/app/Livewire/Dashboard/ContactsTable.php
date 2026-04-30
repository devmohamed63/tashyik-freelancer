<?php

namespace App\Livewire\Dashboard;

use App\Models\Contact;
use App\Utils\Livewire\DataTable;
use App\Utils\Livewire\Table\Button;
use App\Utils\Livewire\Table\Column;
use App\Utils\Livewire\Table\Modal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;
use Livewire\WithPagination;

class ContactsTable extends DataTable
{
    use WithPagination;

    protected $modelClass = Contact::class;

    public array|null $searchableColumns = [
        'subject',
        'name',
        'phone',
        'email',
    ];

    public bool $tableHasStatus = true;

    public array $availableStatusTypes = ['read', 'unread'];

    public string|null $statusFilter = null;

    public function mount()
    {
        $this->authorize('viewAny', Contact::class);
    }

    /**
     * Table Query Builder
     */
    protected function builder(): Builder
    {
        $query = Contact::query()->select([
            'id',
            'name',
            'phone',
            'email',
            'subject',
            'created_at',
            'is_read',
        ]);

        if ($this->statusFilter) {
            if ($this->statusFilter == 'read') {
                $query->where('is_read', true);
            } elseif ($this->statusFilter == 'unread') {
                $query->where('is_read', false);
            }
        }

        return $query;
    }

    public function show($id)
    {
        $this->dispatch('show-result', $id);

        $this->dispatch('showModal', ['id' => 'showResultModal']);
    }


    protected function columns(): Collection|null
    {
        return new Collection([

            Column::name('check')
                ->checkbox(),

            Column::name('subject', __('ui.subject'))
                ->sortable(),

            Column::name('name')
                ->sortable(),

            Column::name('phone', __('validation.attributes.phone'))
                ->callback(function ($contact) {
                    $phone = $contact->phone;
                    $whatsappPhone = ltrim($phone, '0');
                    if (!str_starts_with($whatsappPhone, '966')) {
                        $whatsappPhone = '966' . $whatsappPhone;
                    }
                    
                    return '
                    <div class="flex items-center gap-3">
                        <span dir="ltr">' . $phone . '</span>
                        <div class="flex items-center gap-2 border-r rtl:border-r-0 rtl:border-l border-gray-200 px-2">
                            <a href="https://wa.me/' . $whatsappPhone . '" target="_blank" class="text-green-500 hover:text-green-600 transition-colors" title="مراسلة عبر واتساب">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.082 21.144c-1.579 0-3.095-.39-4.44-1.13l-4.796 1.26 1.284-4.664c-.815-1.393-1.246-3.003-1.246-4.673 0-5.26 4.282-9.542 9.542-9.542 5.261 0 9.542 4.282 9.542 9.542 0 5.26-4.281 9.542-9.542 9.542v.005z"/></svg>
                            </a>
                        </div>
                    </div>';
                }),

            Column::name('email', __('validation.attributes.email'))
                ->callback(function ($contact) {
                    if (!$contact->email) return '-';
                    return '
                    <div class="flex items-center gap-3">
                        <span dir="ltr">' . $contact->email . '</span>
                        <div class="flex items-center gap-2 border-r rtl:border-r-0 rtl:border-l border-gray-200 px-2">
                            <a href="https://mail.google.com/mail/u/0/?view=cm&fs=1&to=' . $contact->email . '" target="_blank" class="text-red-500 hover:text-red-600 transition-colors" title="مراسلة عبر Gmail">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/></svg>
                            </a>
                        </div>
                    </div>';
                }),

            Column::name('is_read', 'حالة الطلب')
                ->callback(function ($contact) {
                    $isRead = $contact->is_read;
                    $textColor = $isRead ? 'text-green-700 bg-green-50 focus:ring-green-500 border-green-200' : 'text-red-700 bg-red-50 focus:ring-red-500 border-red-200';

                    return '<select x-on:change="$wire.updateReadStatus(' . $contact->id . ', $event.target.value)" style="min-width: 140px; white-space: nowrap;" class="block w-36 text-sm font-medium rounded-lg border px-3 py-1.5 focus:outline-none focus:ring-2 ' . $textColor . '">
                                <option value="0" ' . (!$isRead ? 'selected' : '') . '>غير مقروءة</option>
                                <option value="1" ' . ($isRead ? 'selected' : '') . '>مقروءة</option>
                            </select>';
                }),

            Column::name('created_at', __('ui.created_at'))
                ->sortable()
                ->dateFormat(),

            Column::name('show', __('ui.show'))
                ->action()
                ->view('components.dashboard.tables.buttons.show')
                ->wireAction('show'),

            Column::name('delete', __('ui.delete'))
                ->action()
                ->wireAction('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    public function updateReadStatus($id, $status)
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['is_read' => $status == 1]);
    }

    protected function buttons(): Collection|null
    {
        return new Collection([

            Button::name('delete')
                ->type('delete')
                ->view('components.dashboard.tables.buttons.delete'),

        ]);
    }

    protected function dropdowns(): Collection|null
    {
        return new Collection([]);
    }

    protected function modals(): Collection|null
    {
        return new Collection([

            Modal::id('showResultModal')
                ->view('dashboard.contacts.show'),

        ]);
    }

    #[On('refreshTable')]
    public function render()
    {
        return view('livewire.dashboard.general-table', [
            'results' => $this->getResults(),
            'fields' => $this->getFields(),
            'buttons' => $this->getButtons(),
            'dropdowns' => $this->getDropdowns(),
            'modals' => $this->getModals(),
        ]);
    }
}
