<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Fotlist') }} - Admin</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 25%, #2d2d2d 50%, #1a1a1a 75%, #0a0a0a 100%);
                background-attachment: fixed;
                color: #ffffff;
            }

            .nav-bar {
                background: rgba(255, 255, 255, 0.03);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            .glass-card {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }

            .glass-card:hover {
                background: rgba(255, 255, 255, 0.08);
                border-color: rgba(255, 255, 255, 0.2);
            }

            .gradient-text {
                background: linear-gradient(135deg, #ffffff 0%, #b0b0b0 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .btn-primary {
                background: linear-gradient(135deg, #ffffff 0%, #d0d0d0 100%);
                color: #000;
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2);
            }

            .btn-secondary {
                background: rgba(255, 255, 255, 0.1);
                color: #ffffff;
                border: 1px solid rgba(255, 255, 255, 0.3);
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-secondary:hover {
                background: rgba(255, 255, 255, 0.15);
                border-color: rgba(255, 255, 255, 0.5);
                transform: translateY(-2px);
            }

            .btn-danger {
                background: rgba(239, 68, 68, 0.2);
                color: #ef4444;
                border: 1px solid rgba(239, 68, 68, 0.3);
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-danger:hover {
                background: rgba(239, 68, 68, 0.3);
                border-color: rgba(239, 68, 68, 0.5);
                transform: translateY(-2px);
            }

            .nav-link {
                color: #9ca3af;
                transition: all 0.3s ease;
                border-bottom: 2px solid transparent;
            }

            .nav-link:hover {
                color: #ffffff;
            }

            .nav-link.active {
                color: #ffffff;
                border-bottom-color: #ffffff;
            }

            .alert-success {
                background: rgba(16, 185, 129, 0.1);
                border: 1px solid rgba(16, 185, 129, 0.3);
                color: #10b981;
            }

            .alert-error {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #ef4444;
            }

            input, select, textarea {
                background: rgba(255, 255, 255, 0.05);
                border: 1px solid rgba(255, 255, 255, 0.1);
                color: #ffffff;
                transition: all 0.3s ease;
            }

            input:focus, select:focus, textarea:focus {
                background: rgba(255, 255, 255, 0.08);
                border-color: rgba(255, 255, 255, 0.3);
                outline: none;
            }

            input::placeholder, textarea::placeholder {
                color: #6b7280;
            }

            table {
                background: rgba(255, 255, 255, 0.03);
            }

            thead {
                background: rgba(255, 255, 255, 0.05);
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }

            tbody tr {
                border-bottom: 1px solid rgba(255, 255, 255, 0.05);
                transition: all 0.3s ease;
            }

            tbody tr:hover {
                background: rgba(255, 255, 255, 0.05);
            }

            .stat-card {
                background: rgba(255, 255, 255, 0.05);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(255, 255, 255, 0.1);
                transition: all 0.3s ease;
            }

            .stat-card:hover {
                background: rgba(255, 255, 255, 0.08);
                transform: translateY(-5px);
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen">
            <!-- Admin Navigation -->
            <nav class="nav-bar sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('admin.dashboard') }}" class="text-2xl font-bold gradient-text">
                                    📸 Fotlist Admin
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                                    Dashboard
                                </a>
                                <a href="{{ route('admin.photographers.index') }}" class="nav-link {{ request()->routeIs('admin.photographers.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                                    Fotografer
                                </a>
                                <a href="{{ route('admin.albums.index') }}" class="nav-link {{ request()->routeIs('admin.albums.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                                    Album
                                </a>
                                <a href="{{ route('admin.events.index') }}" class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                                    Events
                                </a>
                                <a href="{{ route('admin.revenue.index') }}" class="nav-link {{ request()->routeIs('admin.revenue.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                                    Revenue
                                </a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-medium">
                                    Audit Logs
                                </a>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative flex items-center space-x-4">
                                <span class="text-sm text-gray-300">{{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-gray-400 hover:text-white transition">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="py-8">
                @if(session('success'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                        <div class="alert-success px-4 py-3 rounded-lg" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                        <div class="alert-error px-4 py-3 rounded-lg" role="alert">
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
