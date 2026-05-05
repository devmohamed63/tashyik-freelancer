<!doctype html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="robots" content="noindex, nofollow">

    <title>{{ __('ui.page_not_found') }}</title>

    @vite(['resources/css/dashboard.css'])
</head>

<body>

    <section class="bg-white dark:bg-gray-900">
        <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6 min-h-screen flex items-center justify-center">
            <div class="mx-auto max-w-screen-sm text-center">
                <h1 class="mb-4 text-7xl tracking-tight font-extrabold lg:text-9xl text-brand-600 dark:text-brand-500">404</h1>
                <p class="mb-4 text-3xl tracking-tight font-bold text-gray-900 md:text-4xl dark:text-white">{{ __('ui.page_not_found') }}</p>
                <p class="mb-4 text-lg font-light text-gray-500 dark:text-gray-400">{{ __('ui.404_description') }}</p>
            </div>
        </div>
    </section>

</body>

</html>
