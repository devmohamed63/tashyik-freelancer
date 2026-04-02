<div>
    <div class="space-y-5">

        <!-- Modal loader -->
        <x-dashboard.loaders.centered wire:loading.class.remove="hidden" />

        <!-- Modal title -->
        <x-dashboard.modals.title :value="__('ui.show_contact_request')" />

        <div class="space-y-5">

            <!-- Subject -->
            <x-dashboard.info-label :name="__('validation.attributes.subject')" :value="$contact?->subject" />

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

                <!-- Name -->
                <x-dashboard.info-label :name="__('validation.attributes.name')" :value="$contact?->name" />

                <!-- Phone -->
                <div class="flex flex-col">
                    <x-dashboard.info-label :name="__('validation.attributes.phone')" :value="$contact?->phone" />
                    @if ($contact?->phone)
                        @php
                            $whatsappPhone = ltrim($contact->phone, '0');
                            if (!str_starts_with($whatsappPhone, '966')) $whatsappPhone = '966' . $whatsappPhone;
                        @endphp
                        <a href="https://wa.me/{{ $whatsappPhone }}" target="_blank" class="text-green-500 hover:text-green-600 font-medium inline-flex items-center gap-1 mt-1.5 text-sm" title="مراسلة عبر واتساب">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824zm-3.423-14.416c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm.082 21.144c-1.579 0-3.095-.39-4.44-1.13l-4.796 1.26 1.284-4.664c-.815-1.393-1.246-3.003-1.246-4.673 0-5.26 4.282-9.542 9.542-9.542 5.261 0 9.542 4.282 9.542 9.542 0 5.26-4.281 9.542-9.542 9.542v.005z"/></svg>
                            مراسلة واتساب
                        </a>
                    @endif
                </div>

                <!-- Email -->
                <div class="flex flex-col">
                    <x-dashboard.info-label :name="__('validation.attributes.email')" :value="$contact?->email ?: '-'" />
                    @if ($contact?->email)
                        <a href="https://mail.google.com/mail/u/0/?view=cm&fs=1&to={{ $contact->email }}" target="_blank" class="text-red-500 hover:text-red-600 font-medium inline-flex items-center gap-1 mt-1.5 text-sm" title="مراسلة عبر Gmail">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 5.457v13.909c0 .904-.732 1.636-1.636 1.636h-3.819V11.73L12 16.64l-6.545-4.91v9.273H1.636A1.636 1.636 0 0 1 0 19.366V5.457c0-2.023 2.309-3.178 3.927-1.964L5.455 4.64 12 9.548l6.545-4.91 1.528-1.145C21.69 2.28 24 3.434 24 5.457z"/></svg>
                            إرسال إيميل
                        </a>
                    @endif
                </div>

                <!-- Date -->
                <x-dashboard.info-label :name="__('ui.created_at')" :value="$date" />

            </div>

            <x-dashboard.ui.hr />

            <!-- Message -->
            <x-dashboard.info-label :name="__('validation.attributes.message')" :value="$contact?->message" />

        </div>
    </div>
</div>
