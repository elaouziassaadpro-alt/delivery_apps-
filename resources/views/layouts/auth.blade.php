<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - Login</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/logo/logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="font-sans antialiased text-text-main overflow-hidden">
    <div class="min-h-screen relative flex items-center justify-center bg-gray-50">
        <!-- Background Decorative Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-secondary/10 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-white opacity-20 rotate-12 blur-2xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-lg px-6">
            <div class="text-center mb-10">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-primary rounded-3xl shadow-lg shadow-primary/20 mb-6 rotate-12 hover:rotate-0 transition-transform duration-500">
                    <i data-lucide="truck" class="text-white w-10 h-10"></i>
                </div>
                <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 mb-2">Welcome Back</h1>
                <p class="text-gray-500 font-medium">Please enter your details to sign in</p>
            </div>

            <div class="bg-white/80 backdrop-blur-xl border border-white p-10 rounded-[2.5rem] shadow-2xl shadow-gray-200/50">
                {{ $slot }}
            </div>

            <div class="text-center mt-8">
                <p class="text-gray-400 text-sm font-medium">
                    &copy; {{ date('Y') }} {{ config('app.name') }} Delivery System. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
