<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FDE Admission Portal — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        * { font-family: 'Inter', sans-serif; }

        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            70%  { box-shadow: 0 0 0 10px rgba(239,68,68,0); }
            100% { box-shadow: 0 0 0 0 rgba(239,68,68,0); }
        }
        .help-pulse { animation: pulse-ring 2.5s ease-in-out infinite; }

        video::-webkit-media-controls { opacity: 1; }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-950 via-blue-900 to-blue-800 flex items-center justify-center p-4 sm:p-8">

    <div class="w-full max-w-6xl flex flex-col lg:flex-row gap-8 items-center">

        {{-- ══ LEFT — Login Form ══ --}}
        <div class="w-full lg:w-[420px] shrink-0">
            <div class="bg-white rounded-2xl shadow-2xl w-full px-8 py-10">

                <div class="text-center mb-8">
                    <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl mb-4 shadow-lg p-1.5 border border-gray-100">
                        <img src="{{ asset('images/state-emblem-pakistan.png') }}"
                             alt="Government of Pakistan State Emblem"
                             class="w-full h-full object-contain drop-shadow-sm" />
                    </div>
                    <h1 class="text-2xl font-bold text-blue-900">Federal Directorate of Education</h1>
                    <p class="text-gray-400 text-sm mt-1">Admission Portal 2026-27</p>
                </div>

                @if ($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
                        ⚠️ {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            autocomplete="email" placeholder="your@email.com"
                            class="w-full bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition" />
                    </div>

                    <div class="mb-5">
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <div class="relative">
                            <input id="password" type="password" name="password" required
                                autocomplete="current-password"
                                class="w-full bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 pr-10 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-300 transition" />
                            <button type="button" onclick="togglePwd()"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 text-xs select-none">
                                👁
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600">
                            <span class="text-sm text-gray-600">Remember Me</span>
                        </label>
                        <a href="{{ route('password.request') }}"
                            class="text-sm font-semibold text-blue-700 hover:text-blue-900 transition">
                            Forgot Password?
                        </a>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold rounded-xl py-3 text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Login
                    </button>
                </form>

                {{-- ── Demo Help Notice ── --}}
                <div class="mt-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3.5 flex items-start gap-3">
                    <span class="text-xl mt-0.5 shrink-0">🎬</span>
                    <div>
                        <p class="text-sm font-bold text-amber-800">
                            Need help using the Admission Portal?
                        </p>
                        <p class="text-xs text-amber-700 mt-0.5 leading-relaxed">
                            Watch the <strong>training demo video</strong> on the right to learn how to submit admissions, upload documents, and complete your profile.
                        </p>
                        <button onclick="scrollToVideo()"
                            class="mt-2 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 hover:text-amber-900 underline underline-offset-2 lg:hidden">
                            ▶ Watch Demo Video ↓
                        </button>
                    </div>
                </div>

            </div>
        </div>

        {{-- ══ RIGHT — Demo Video ══ --}}
        <div id="video-section" class="w-full flex-1 min-w-0">

            {{-- Header --}}
            <div class="mb-4 text-center lg:text-left">
                <span class="inline-flex items-center gap-2 bg-white/10 text-white text-xs font-bold uppercase tracking-widest px-3 py-1.5 rounded-full border border-white/20 mb-3">
                    <span class="w-2 h-2 bg-red-400 rounded-full help-pulse"></span>
                    Training Demo
                </span>
                <h2 class="text-2xl font-bold text-white leading-snug">
                    How to Use the<br>
                    <span class="text-blue-300">FDE Admission Portal</span>
                </h2>
                <p class="text-blue-200 text-sm mt-2">
                    <strong class="text-white">Watch this video before logging in</strong> — it covers everything you need to know as a Head of Institution.
                </p>
            </div>

            {{-- Video Player --}}
            <div class="bg-black rounded-2xl overflow-hidden shadow-2xl border border-white/10">
                <video
                    id="demo-video"
                    controls
                    preload="metadata"
                    class="w-full"
                    style="max-height: 480px; display: block;"
                    poster="">
                    <source src="{{ asset('videos/FDE_HOI_Training_Video.mp4') }}" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
            </div>

            {{-- Quick Tips below video --}}
            <div class="mt-4 grid grid-cols-3 gap-3">
                <div class="bg-white/10 rounded-xl px-3 py-3 text-center border border-white/10">
                    <p class="text-2xl mb-1">🔐</p>
                    <p class="text-white text-xs font-semibold">Login</p>
                    <p class="text-blue-200 text-[10px] mt-0.5">Use your personal email provided by FDE</p>
                </div>
                <div class="bg-white/10 rounded-xl px-3 py-3 text-center border border-white/10">
                    <p class="text-2xl mb-1">📋</p>
                    <p class="text-white text-xs font-semibold">Fill Admissions</p>
                    <p class="text-blue-200 text-[10px] mt-0.5">Enter daily admission counts for your school</p>
                </div>
                <div class="bg-white/10 rounded-xl px-3 py-3 text-center border border-white/10">
                    <p class="text-2xl mb-1">📤</p>
                    <p class="text-white text-xs font-semibold">Submit Reports</p>
                    <p class="text-blue-200 text-[10px] mt-0.5">Upload documents and track your progress</p>
                </div>
            </div>

        </div>

    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            window.togglePwd = function () {
                const field = document.getElementById("password");
                if (!field) return;
                field.type = field.type === "password" ? "text" : "password";
            };

            window.scrollToVideo = function () {
                document.getElementById("video-section").scrollIntoView({ behavior: "smooth" });
            };
        });
    </script>

</body>

</html>
