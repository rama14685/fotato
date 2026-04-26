<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Fotlist') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Face API.js for face detection and recognition -->
        <script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-gray-950 text-gray-100 relative min-h-screen">
        
        <div class="fixed top-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/20 rounded-full mix-blend-screen filter blur-[120px] opacity-80 pointer-events-none z-0"></div>
        <div class="fixed bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/20 rounded-full mix-blend-screen filter blur-[120px] opacity-80 pointer-events-none z-0"></div>

        <div class="min-h-screen relative z-10">
            @include('layouts.navigation')

            @isset($header)
                <header class="bg-gray-900/50 backdrop-blur-md border-b border-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>