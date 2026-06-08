<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'fotlist') }} - Admin</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <style>
            body {
                background: #0d061a;
                background-attachment: fixed;
                color: #ffffff;
            }

            .nav-bar {
                background: rgba(13, 6, 26, 0.85);
                backdrop-filter: blur(10px);
                border-bottom: 1px solid rgba(168, 85, 247, 0.15);
            }

            .glass-card {
                background: rgba(168, 85, 247, 0.03);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(168, 85, 247, 0.1);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .glass-card:hover {
                background: rgba(168, 85, 247, 0.08);
                border-color: rgba(168, 85, 247, 0.3);
                box-shadow: 0 10px 30px rgba(168, 85, 247, 0.1);
            }

            .gradient-text {
                background: linear-gradient(135deg, #ffffff 0%, #a855f7 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .btn-primary {
                background: linear-gradient(135deg, #5A2A8F 0%, #8A4FFF 100%);
                color: #ffffff;
                font-weight: 600;
                transition: all 0.3s ease;
                border: none;
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, #6d30b0 0%, #9b5cff 100%);
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(168, 85, 247, 0.3);
            }

            .btn-secondary {
                background: rgba(168, 85, 247, 0.05);
                color: #c084fc;
                border: 1px solid rgba(168, 85, 247, 0.3);
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-secondary:hover {
                background: rgba(168, 85, 247, 0.15);
                border-color: rgba(168, 85, 247, 0.5);
                transform: translateY(-2px);
            }

            .btn-danger {
                background: rgba(239, 68, 68, 0.1);
                color: #f87171;
                border: 1px solid rgba(239, 68, 68, 0.3);
                font-weight: 600;
                transition: all 0.3s ease;
            }

            .btn-danger:hover {
                background: rgba(239, 68, 68, 0.2);
                border-color: rgba(239, 68, 68, 0.5);
                transform: translateY(-2px);
            }

            .nav-link {
                color: #d8b4fe;
                transition: all 0.3s ease;
                border-bottom: 2px solid transparent;
            }

            .nav-link:hover {
                color: #ffffff;
            }

            .nav-link.active {
                color: #ffffff;
                border-bottom-color: #a855f7;
            }

            .alert-success {
                background: rgba(16, 185, 129, 0.1);
                border: 1px solid rgba(16, 185, 129, 0.3);
                color: #34d399;
            }

            .alert-error {
                background: rgba(239, 68, 68, 0.1);
                border: 1px solid rgba(239, 68, 68, 0.3);
                color: #f87171;
            }

            input, select, textarea {
                background: rgba(31, 14, 61, 0.4);
                border: 1px solid rgba(168, 85, 247, 0.2);
                color: #ffffff;
                transition: all 0.3s ease;
            }

            input:focus, select:focus, textarea:focus {
                background: rgba(31, 14, 61, 0.6);
                border-color: rgba(168, 85, 247, 0.5);
                outline: none;
            }

            input::placeholder, textarea::placeholder {
                color: #7c3aed;
            }

            table {
                background: rgba(168, 85, 247, 0.02);
            }

            thead {
                background: rgba(168, 85, 247, 0.05);
                border-bottom: 1px solid rgba(168, 85, 247, 0.1);
            }

            tbody tr {
                border-bottom: 1px solid rgba(168, 85, 247, 0.05);
                transition: all 0.3s ease;
            }

            tbody tr:hover {
                background: rgba(168, 85, 247, 0.05);
            }

            .stat-card {
                background: rgba(168, 85, 247, 0.03);
                backdrop-filter: blur(10px);
                border: 1px solid rgba(168, 85, 247, 0.1);
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }

            .stat-card:hover {
                background: rgba(168, 85, 247, 0.08);
                border-color: rgba(168, 85, 247, 0.3);
                transform: translateY(-5px);
                box-shadow: 0 10px 30px rgba(168, 85, 247, 0.1);
            }

            /* Override Tailwind utility classes to match FOTATO theme */
            .bg-white {
                background-color: rgba(168, 85, 247, 0.03) !important;
                backdrop-filter: blur(12px) !important;
                border: 1px solid rgba(168, 85, 247, 0.1) !important;
                color: #ffffff !important;
            }
            .text-gray-900 {
                color: #ffffff !important;
            }
            .text-gray-800 {
                color: #f3e8ff !important;
            }
            .text-gray-700 {
                color: #e9d5ff !important;
            }
            .text-gray-600 {
                color: rgba(216, 180, 254, 0.6) !important;
            }
            .text-gray-500 {
                color: rgba(216, 180, 254, 0.4) !important;
            }
            .text-gray-400 {
                color: rgba(216, 180, 254, 0.5) !important;
            }
            .bg-gray-50 {
                background-color: rgba(168, 85, 247, 0.05) !important;
            }
            .hover\:bg-gray-50:hover {
                background-color: rgba(168, 85, 247, 0.08) !important;
            }
            .border-gray-200, .border-gray-300 {
                border-color: rgba(168, 85, 247, 0.2) !important;
            }
            .divide-y > :not([hidden]) ~ :not([hidden]) {
                border-color: rgba(168, 85, 247, 0.1) !important;
            }
            .text-blue-600 {
                color: #c084fc !important;
            }
            .text-blue-600:hover {
                color: #a855f7 !important;
            }
            .text-blue-500 {
                color: #c084fc !important;
            }
            .text-blue-500:hover {
                color: #a855f7 !important;
            }
            .bg-blue-600 {
                background: linear-gradient(135deg, #5A2A8F 0%, #8A4FFF 100%) !important;
                border: none !important;
                color: #ffffff !important;
            }
            .bg-blue-600:hover {
                background: linear-gradient(135deg, #6d30b0 0%, #9b5cff 100%) !important;
                box-shadow: 0 10px 30px rgba(168, 85, 247, 0.3) !important;
            }
            .bg-gray-600 {
                background: rgba(168, 85, 247, 0.05) !important;
                border: 1px solid rgba(168, 85, 247, 0.3) !important;
                color: #c084fc !important;
            }
            .bg-gray-600:hover {
                background: rgba(168, 85, 247, 0.15) !important;
                border-color: rgba(168, 85, 247, 0.5) !important;
            }
            .bg-green-500, .from-green-500 {
                background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
            }
            .bg-blue-500, .from-blue-500 {
                background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            }
            .bg-purple-500, .from-purple-500 {
                background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%) !important;
            }
            .bg-yellow-500, .from-yellow-500 {
                background: linear-gradient(135deg, #f59e0b 0%, #d97706) !important;
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen relative">
            <!-- Global Spotlights -->
            <div class="fixed top-[-10%] left-[-10%] w-[500px] h-[500px] bg-purple-600/10 rounded-full mix-blend-screen filter blur-[120px] opacity-80 pointer-events-none z-0"></div>
            <div class="fixed bottom-[-10%] right-[-10%] w-[500px] h-[500px] bg-blue-600/10 rounded-full mix-blend-screen filter blur-[120px] opacity-80 pointer-events-none z-0"></div>

            <!-- Admin Navigation -->
            <nav class="nav-bar sticky top-0 z-50">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="shrink-0 flex items-center">
                                <a href="{{ route('admin.dashboard') }}" class="text-2xl font-black font-display tracking-wider text-white hover:text-purple-300 transition-colors">
                                    FOTATO Admin
                                </a>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-semibold">
                                    Dashboard
                                </a>
                                <a href="{{ route('admin.photographers.index') }}" class="nav-link {{ request()->routeIs('admin.photographers.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-semibold">
                                    Fotografer
                                </a>
                                <a href="{{ route('admin.albums.index') }}" class="nav-link {{ request()->routeIs('admin.albums.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-semibold">
                                    Album
                                </a>
                                <a href="{{ route('admin.events.index') }}" class="nav-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-semibold">
                                    Events
                                </a>
                                <a href="{{ route('admin.revenue.index') }}" class="nav-link {{ request()->routeIs('admin.revenue.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-semibold">
                                    Revenue
                                </a>
                                <a href="{{ route('admin.audit-logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit-logs.*') ? 'active' : '' }} inline-flex items-center px-1 pt-1 text-sm font-semibold">
                                    Audit Logs
                                </a>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <div class="ml-3 relative flex items-center space-x-4">
                                <span class="text-sm text-gray-300 font-semibold">{{ Auth::user()->name }}</span>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="text-sm text-purple-300 hover:text-white font-semibold transition">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="py-8 relative z-10">
                @if(session('success'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                        <div class="alert-success px-4 py-3 rounded-lg font-medium text-sm" role="alert">
                            <span class="block sm:inline">✓ {{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mb-4">
                        <div class="alert-error px-4 py-3 rounded-lg font-medium text-sm" role="alert">
                            <span class="block sm:inline">✗ {{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </body>
</html>
