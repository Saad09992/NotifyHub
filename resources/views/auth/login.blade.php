@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8">
        <h1 class="text-2xl font-semibold text-gray-900 mb-1">Log in</h1>
        <p class="text-sm text-gray-500 mb-6">Enter your credentials to access your account.</p>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:text-sm @error('email') border-rose-500 @enderror"
                >
                @error('email')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    autocomplete="current-password"
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-gray-900 focus:ring-gray-900 sm:text-sm @error('password') border-rose-500 @enderror"
                >
                @error('password')
                    <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-900 text-white text-sm font-medium rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900">
                Log in
            </button>
        </form>

        <p class="mt-6 text-sm text-gray-500 text-center">
            Don't have an account?
            <a href="{{ route('register') }}" class="text-gray-900 font-medium hover:underline">Register</a>
        </p>
    </div>
</div>
@endsection
