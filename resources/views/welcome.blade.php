@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto text-center py-16">
    <h1 class="text-4xl font-bold text-gray-900 tracking-tight">NotifyHub</h1>
    <p class="mt-4 text-lg text-gray-600">A simple hub for your notifications.</p>

    <div class="mt-8 flex items-center justify-center gap-3">
        @auth
            <a href="{{ route('dashboard') }}" class="px-5 py-2.5 rounded-md bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">
                Go to dashboard
            </a>
        @else
            <a href="{{ route('register') }}" class="px-5 py-2.5 rounded-md bg-gray-900 text-white text-sm font-medium hover:bg-gray-800">
                Get started
            </a>
            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-md border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-100">
                Log in
            </a>
        @endauth
    </div>
</div>
@endsection
