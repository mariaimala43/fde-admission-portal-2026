<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FDE Admission Portal 2026 — Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-lg p-10 w-full max-w-md">

        {{-- Header --}}
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-blue-900">
                Federal Directorate of Education
            </h1>
            <p class="text-gray-500 text-sm mt-1">
                Admission Portal 2026-27
            </p>
        </div>

        {{-- Title --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-blue-100 rounded-full mb-3">
                <svg class="w-7 h-7 text-blue-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">Forgot Password?</h2>
            <p class="text-sm text-gray-500 mt-1">
                Enter your email address and we'll send you a link to reset your password.
            </p>
        </div>

        {{-- Success Message --}}
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email Address
                </label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Enter your registered email" />
            </div>

            <button type="submit"
                class="w-full bg-blue-900 text-white py-2.5 rounded-lg font-semibold text-sm hover:bg-blue-800 transition">
                Send Reset Link
            </button>
        </form>

        {{-- Back to Login --}}
        <div class="mt-6 text-center">
            <a href="{{ route('login') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Login
            </a>
        </div>

    </div>

</body>

</html>
