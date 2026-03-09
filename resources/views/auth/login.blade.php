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

        .cred-card { cursor: pointer; transition: all 0.15s ease; }
        .cred-card:hover { transform: translateX(3px); }
        .cred-card:hover .arrow { opacity: 1; }
        .arrow { opacity: 0; transition: opacity 0.15s; }

        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.5} }
        .dev-pulse { animation: blink 2s ease-in-out infinite; }

        .creds-list { max-height: calc(100vh - 140px); overflow-y: auto; }
        .creds-list::-webkit-scrollbar { width: 4px; }
        .creds-list::-webkit-scrollbar-track { background: #f1f5f9; }
        .creds-list::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    </style>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-4">

@php
    $roleConfig = [
        'fde_cell' => [
            'label'      => 'FDE Cell',
            'card'       => 'border-blue-100 bg-blue-50',
            'badge'      => 'text-blue-700 bg-blue-100',
            'arrow'      => 'text-blue-400',
            'divider_bg' => 'bg-blue-900',
        ],
        'director' => [
            'label'      => 'Director',
            'card'       => 'border-purple-100 bg-purple-50',
            'badge'      => 'text-purple-700 bg-purple-100',
            'arrow'      => 'text-purple-400',
            'divider_bg' => 'bg-purple-800',
        ],
        'aeo' => [
            'label'      => 'AEO',
            'card'       => 'border-green-100 bg-green-50',
            'badge'      => 'text-green-700 bg-green-100',
            'arrow'      => 'text-green-400',
            'divider_bg' => 'bg-green-800',
        ],
        'hoi' => [
            'label'      => 'HOI',
            'card'       => 'border-orange-100 bg-orange-50',
            'badge'      => 'text-orange-700 bg-orange-100',
            'arrow'      => 'text-orange-400',
            'divider_bg' => 'bg-orange-800',
        ],
    ];

    $roleOrder = ['fde_cell', 'director', 'aeo', 'hoi'];
@endphp

<div class="flex gap-6 w-full max-w-5xl items-start">

    {{-- ══ LEFT — Test Credentials (dynamic from DB) ══ --}}
    <div class="w-72 shrink-0 sticky top-4">

        <div class="flex items-center gap-2 mb-3">
            <span class="dev-pulse inline-flex items-center gap-1.5 bg-amber-100 border border-amber-300 text-amber-700 text-[10px] font-bold px-2.5 py-1 rounded-full uppercase tracking-widest">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 inline-block"></span>
                Dev · Test Accounts
            </span>
            <span class="text-[10px] text-gray-400">{{ $testUsers->flatten(1)->count() }} active</span>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-4 py-2.5 bg-gray-50 border-b border-gray-100">
                <p class="text-[11px] text-gray-400 font-medium">Click any card to auto-fill credentials</p>
            </div>

            <div class="creds-list p-2.5 space-y-1.5">

                @foreach ($roleOrder as $role)
                    @if ($testUsers->has($role))
                        @php $cfg = $roleConfig[$role] ?? ['label'=>strtoupper($role),'card'=>'border-gray-100 bg-gray-50','badge'=>'text-gray-600 bg-gray-100','arrow'=>'text-gray-400','divider_bg'=>'bg-gray-700']; @endphp

                        {{-- Role section divider --}}
                        <div class="pt-1 pb-0.5 px-1 flex items-center gap-2">
                            <span class="text-[9px] uppercase tracking-widest text-gray-400 font-semibold">
                                {{ $cfg['label'] }} Accounts
                            </span>
                            <span class="text-[9px] text-gray-300">({{ count($testUsers[$role]) }})</span>
                        </div>

                        @foreach ($testUsers[$role] as $account)
                            <div class="cred-card rounded-xl border {{ $cfg['card'] }} px-3 py-2.5"
                                 onclick="fillCreds('{{ $account['email'] }}','{{ $account['password'] }}')">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold uppercase tracking-wider {{ $cfg['badge'] }} px-2 py-0.5 rounded-full">
                                        {{ $cfg['label'] }}
                                    </span>
                                    <span class="arrow text-[10px] {{ $cfg['arrow'] }} font-medium">→ fill</span>
                                </div>
                                <p class="text-xs font-semibold text-gray-700 mt-1.5 truncate" title="{{ $account['email'] }}">
                                    {{ $account['email'] }}
                                </p>
                                <p class="text-[10px] text-gray-400 truncate leading-snug" title="{{ $account['description'] }}">
                                    {{ $account['description'] }}
                                </p>
                            </div>
                        @endforeach
                    @endif
                @endforeach

            </div>

            {{-- Password legend --}}
            <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50 flex justify-between items-center gap-2">
                <p class="text-[10px] text-gray-400">FDE Admin: <code class="bg-gray-200 px-1 rounded text-gray-600">Admin@1234</code></p>
                <p class="text-[10px] text-gray-400">Others: <code class="bg-gray-200 px-1 rounded text-gray-600">Test@1234</code></p>
            </div>
        </div>

        <p class="text-[10px] text-gray-400 text-center mt-2">⚠️ Remove before production</p>
    </div>

    {{-- ══ RIGHT — Login Form ══ --}}
    <div class="flex-1 flex justify-center items-center" style="min-height: calc(100vh - 2rem);">
        <div class="bg-white rounded-2xl shadow-md w-full max-w-md px-10 py-10">

            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-blue-900">Federal Directorate of Education</h1>
                <p class="text-gray-400 text-sm mt-1">Admission Portal 2026-27</p>
            </div>

            @if($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl px-4 py-3">
                    {{ $errors->first() }}
                </div>
            @endif

            @if(session('status'))
                <div class="mb-5 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Email Address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}"
                        required autocomplete="email" placeholder="your@fde.edu.pk"
                        class="w-full bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"/>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                    <div class="relative">
                        <input id="password" type="password" name="password"
                            required autocomplete="current-password"
                            class="w-full bg-blue-50 border border-blue-100 rounded-xl px-4 py-3 pr-10 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"/>
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
                    <a href="{{ route('password.request') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900 transition">
                        Forgot Password?
                    </a>
                </div>

                <button type="submit"
                    class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold rounded-xl py-3 text-sm transition focus:outline-none focus:ring-2 focus:ring-blue-300">
                    Login
                </button>
            </form>
        </div>
    </div>

</div>

<script>
    function fillCreds(email, password) {
        document.getElementById('email').value    = email;
        document.getElementById('password').value = password;
        const card = event.currentTarget;
        card.style.transition  = 'box-shadow 0.2s, transform 0.1s';
        card.style.boxShadow   = '0 0 0 2px rgba(59,130,246,0.5)';
        card.style.transform   = 'translateX(6px)';
        setTimeout(() => {
            card.style.boxShadow = '';
            card.style.transform = '';
        }, 500);
    }

    function togglePwd() {
        const f = document.getElementById('password');
        f.type = f.type === 'password' ? 'text' : 'password';
    }
</script>

</body>
</html>
