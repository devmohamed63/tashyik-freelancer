<div>
    <img class="block dark:hidden {{ $attributes->get('class') }}" src="{{ $lightMode }}" alt="Logo" />
    <img class="hidden dark:block {{ $attributes->get('class') }}" src="{{ $darkMode }}" alt="Logo" />
</div>
