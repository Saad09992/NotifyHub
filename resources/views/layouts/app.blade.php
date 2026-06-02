<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'NotifyHub') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body
    x-data="{ sidebarOpen: false }"
    class="font-sans antialiased min-h-screen bg-gray-50 text-gray-900"
>
    <header class="border-b border-gray-200 bg-white sticky top-0 z-30">
        <nav class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @auth
                    <button
                        type="button"
                        @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-1.5 rounded text-gray-600 hover:bg-gray-100"
                        aria-label="Toggle sidebar"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                @endauth
                <a href="{{ url('/') }}" class="font-semibold text-gray-900 flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-7 h-7 rounded bg-gray-900 text-white text-xs font-bold">N</span>
                    NotifyHub
                </a>
            </div>

            <div class="flex items-center gap-2 text-sm">
                @auth
                    <span class="hidden sm:inline text-gray-500 px-2">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="px-3 py-1.5 rounded border border-gray-300 hover:bg-gray-100 text-gray-700">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="px-3 py-1.5 rounded hover:bg-gray-100 text-gray-700">Log in</a>
                    <a href="{{ route('register') }}" class="px-3 py-1.5 rounded bg-gray-900 text-white hover:bg-gray-800">Register</a>
                @endauth
            </div>
        </nav>
    </header>

    @auth
        <div class="flex">
            {{-- Mobile sidebar backdrop --}}
            <div
                x-show="sidebarOpen"
                @click="sidebarOpen = false"
                x-transition.opacity
                class="fixed inset-0 z-30 bg-black/40 lg:hidden"
                style="display: none;"
            ></div>

            {{-- Sidebar --}}
            <aside
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
                class="fixed lg:sticky top-14 left-0 z-40 lg:translate-x-0 transition-transform w-64 h-[calc(100vh-3.5rem)] bg-white border-r border-gray-200 flex flex-col"
            >
                <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                    @php
                        $navItems = [
                            ['label' => 'Dashboard',     'route' => 'dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10a1 1 0 001 1h3v-6h6v6h3a1 1 0 001-1V10'],
                            ['label' => 'Notifications', 'route' => null,        'icon' => 'M15 17h5l-1.4-1.4A2 2 0 0118 14.2V11a6 6 0 10-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                            ['label' => 'Channels',      'route' => null,        'icon' => 'M4 6h16M4 12h16M4 18h7'],
                            ['label' => 'Templates',     'route' => null,        'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.6a2 2 0 011.4.6L19 8.4a2 2 0 01.6 1.4V19a2 2 0 01-2 2z'],
                            ['label' => 'Settings',      'route' => null,        'icon' => 'M10.3 3.6a1.7 1.7 0 013.4 0l.2.8a1.7 1.7 0 002.4 1l.7-.4a1.7 1.7 0 012.4 2.4l-.4.7a1.7 1.7 0 001 2.4l.8.2a1.7 1.7 0 010 3.4l-.8.2a1.7 1.7 0 00-1 2.4l.4.7a1.7 1.7 0 01-2.4 2.4l-.7-.4a1.7 1.7 0 00-2.4 1l-.2.8a1.7 1.7 0 01-3.4 0l-.2-.8a1.7 1.7 0 00-2.4-1l-.7.4a1.7 1.7 0 01-2.4-2.4l.4-.7a1.7 1.7 0 00-1-2.4l-.8-.2a1.7 1.7 0 010-3.4l.8-.2a1.7 1.7 0 001-2.4l-.4-.7A1.7 1.7 0 016.7 4l.7.4a1.7 1.7 0 002.4-1l.2-.8zM12 15a3 3 0 100-6 3 3 0 000 6z'],
                        ];
                    @endphp

                    @foreach ($navItems as $item)
                        @php
                            $href = $item['route'] ? route($item['route']) : '#';
                            $active = $item['route'] && request()->routeIs($item['route']);
                        @endphp
                        <a
                            href="{{ $href }}"
                            class="flex items-center gap-3 px-3 py-2 rounded-md text-sm font-medium transition
                                {{ $active
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900' }}"
                        >
                            <svg class="w-5 h-5 flex-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="border-t border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gray-900 text-white flex items-center justify-center text-sm font-semibold">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <main class="flex-1 min-w-0 px-4 lg:px-8 py-8 max-w-6xl mx-auto w-full">
                @yield('content')
                {{ $slot ?? '' }}
            </main>
        </div>
    @else
        <main class="max-w-5xl mx-auto px-4 py-8">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    @endauth

    @include('partials.toasts')
</body>
</html>
