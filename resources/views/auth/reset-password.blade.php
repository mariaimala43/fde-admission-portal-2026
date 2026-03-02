<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FDE Admission Portal 2026 — Reset Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            <div class="inline-flex items-center justify-center w-14 h-14 bg-green-100 rounded-full mb-3">
                <svg class="w-7 h-7 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                </svg>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">Reset Password</h2>
            <p class="text-sm text-gray-500 mt-1">
                Enter your new password below.
            </p>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        {{-- Form --}}
        <form method="POST" action="{{ route('password.update') }}" x-data="{ showPass: false, showConfirm: false }">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}" />
            <input type="hidden" name="email" value="{{ $email }}" />

            {{-- Email (read-only display) --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Email Address
                </label>
                <input type="email" value="{{ $email }}" disabled
                    class="w-full border border-gray-200 bg-gray-50 rounded-lg px-4 py-2.5 text-sm text-gray-500 cursor-not-allowed" />
            </div>

            {{-- New Password --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    New Password
                </label>
                <div class="relative">
                    <input :type="showPass ? 'text' : 'password'" name="password" required minlength="8"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10"
                        placeholder="Minimum 8 characters" />
                    <button type="button" @click="showPass = !showPass"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg x-show="!showPass" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showPass" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Confirm Password --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm New Password
                </label>
                <div class="relative">
                    <input :type="showConfirm ? 'text' : 'password'" name="password_confirmation" required minlength="8"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 pr-10"
                        placeholder="Re-enter your new password" />
                    <button type="button" @click="showConfirm = !showConfirm"
                        class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg x-show="!showConfirm" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <svg x-show="showConfirm" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                class="w-full bg-blue-900 text-white py-2.5 rounded-lg font-semibold text-sm hover:bg-blue-800 transition">
                Reset Password
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
