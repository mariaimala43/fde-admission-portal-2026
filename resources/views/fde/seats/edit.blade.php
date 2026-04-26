{{-- SAVE AS: resources/views/fde/seats/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Seat Config — ' . $institution->name)

@section('content')

    {{-- ── Header ─────────────────────────────────────────────────────── --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">{{ $institution->name }}</h2>
            <p class="text-sm text-gray-500 mt-1">
                {{ $institution->code }} · {{ ucfirst($institution->type) }} ·
                Seat Configuration
                @if ($academicYear)
                    · <span class="font-medium text-blue-700">{{ $academicYear->name }}</span>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Sync missing classes (fixes schools whose class list was truncated before SchoolClassHelper fix) --}}
            <form method="POST" action="{{ route('fde.seats.sync', $institution) }}"
                  onsubmit="return confirm('This will add any missing classes for a {{ addslashes($institution->type) }} school (seats set to 0). Continue?')">
                @csrf
                <button type="submit"
                    class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition font-medium">
                    🔄 Sync Missing Classes
                </button>
            </form>
            <a href="{{ route('fde.seats.index') }}"
                class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
                ← Back to All Schools
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 mb-5 text-sm">✅
            {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">❌ {{ session('error') }}
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $error)
                <p>❌ {{ $error }}</p>
            @endforeach
        </div>
    @endif

    @php $isLocked = $institution->seats_locked_at !== null; @endphp

    {{-- ── Lock Banner ──────────────────────────────────────────────── --}}
    @if ($isLocked)
        <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-5 flex justify-between items-center">
            <div>
                <p class="text-sm font-semibold text-green-800">
                    🔒 Seat configuration is <strong>locked</strong>
                </p>
                <p class="text-xs text-green-700 mt-0.5">
                    Locked by <strong>{{ $institution->seatsLockedBy?->name }}</strong>
                    on {{ $institution->seats_locked_at->format('d M Y, g:i A') }}.
                    HOI cannot change class setup while locked.
                </p>
            </div>

            {{-- Unlock modal trigger --}}
            <button type="button" onclick="document.getElementById('unlockModal').style.display='flex'"
                class="text-xs px-4 py-2 bg-white border border-green-300 text-green-700 rounded-lg hover:bg-green-50 transition font-medium">
                🔓 Unlock
            </button>
        </div>
    @else
        <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-5 py-4 mb-5 flex justify-between items-center">
            <div>
                <p class="text-sm font-semibold text-yellow-800">🟡 Seat configuration is <strong>unlocked</strong></p>
                <p class="text-xs text-yellow-700 mt-0.5">
                    HOI can still change class setup. Lock after confirming seat counts are final.
                </p>
            </div>
            <form method="POST" action="{{ route('fde.seats.lock', $institution) }}"
                onsubmit="return confirm('Lock seat configuration for {{ addslashes($institution->name) }}? The HOI will no longer be able to add or remove classes.')">
                @csrf
                <button type="submit"
                    class="text-xs px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium">
                    🔒 Lock Configuration
                </button>
            </form>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ── Seat Editor ─────────────────────────────────────────────── --}}
        <div class="lg:col-span-2">
            @if ($classes->isEmpty())
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-10 text-center text-gray-400 text-sm">
                    <p class="text-3xl mb-3">📭</p>
                    <p class="font-medium text-gray-600">No classes configured yet</p>
                    <p class="mt-1 mb-4">The HOI needs to complete class setup first, or use Sync to auto-create the expected classes.</p>
                    <form method="POST" action="{{ route('fde.seats.sync', $institution) }}"
                          onsubmit="return confirm('Create all expected classes for this {{ addslashes($institution->type) }} school with 0 seats?')">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition font-medium">
                            🔄 Sync Classes from School Type
                        </button>
                    </form>
                </div>
            @else
                <form method="POST" action="{{ route('fde.seats.update', $institution) }}">
                    @csrf
                    @method('PUT')

                    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center">
                            <h3 class="text-sm font-bold text-gray-800">Class Seat Allocation</h3>
                            <span class="text-xs text-gray-400">
                                Total: <strong id="totalDisplay"
                                    class="text-blue-700">{{ $classes->sum('total_seats') }}</strong> seats
                            </span>
                        </div>

                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 bg-gray-50">
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Class</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Existing
                                        Enrollment</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                                        Authorized Seats</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">
                                        Available Now</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($classes as $i => $ic)
                                    <tr class="border-b border-gray-50 hover:bg-gray-50">
                                        <td class="px-4 py-3 font-medium text-gray-800">
                                            {{ $ic->classModel->name }}
                                            <input type="hidden" name="seats[{{ $i }}][class_id]"
                                                value="{{ $ic->class_id }}">
                                        </td>
                                        <td class="px-4 py-3 text-center text-gray-600">
                                            {{ number_format($ic->existing_enrollment) }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if ($isLocked)
                                                <span class="font-semibold text-blue-700">{{ $ic->total_seats }}</span>
                                                <input type="hidden" name="seats[{{ $i }}][total_seats]"
                                                    value="{{ $ic->total_seats }}">
                                            @else
                                                <input type="number" name="seats[{{ $i }}][total_seats]"
                                                    value="{{ $ic->total_seats }}" min="0" max="9999"
                                                    class="seat-input w-24 text-center border border-gray-200 rounded-lg px-2 py-1.5 text-sm
                                                              focus:outline-none focus:ring-2 focus:ring-blue-400"
                                                    required>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @php $avail = $ic->available ?? 0; @endphp
                                            <span
                                                class="font-semibold {{ $avail > 0 ? 'text-green-600' : 'text-red-500' }}">
                                                {{ $avail }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-gray-200 bg-gray-50">
                                    <td class="px-4 py-3 font-bold text-gray-800">Total</td>
                                    <td class="px-4 py-3 text-center font-semibold text-gray-700">
                                        {{ number_format($classes->sum('existing_enrollment')) }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-bold text-blue-700" id="totalFooter">
                                        {{ $classes->sum('total_seats') }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-semibold text-green-600">
                                        {{ $classes->sum('available') }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        @unless ($isLocked)
                            <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                                <button type="submit"
                                    class="px-6 py-2.5 bg-blue-900 text-white text-sm font-semibold rounded-lg hover:bg-blue-800 transition">
                                    💾 Save Seat Configuration
                                </button>
                            </div>
                        @endunless
                    </div>
                </form>
            @endif
        </div>

        {{-- ── Summary Card ─────────────────────────────────────────────── --}}
        <div class="space-y-4">

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
                <h4 class="text-sm font-bold text-gray-800 mb-4">Summary</h4>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Total Authorized Seats</dt>
                        <dd class="font-semibold text-blue-700">{{ number_format($classes->sum('total_seats')) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Existing Enrollment</dt>
                        <dd class="font-semibold text-gray-700">{{ number_format($classes->sum('existing_enrollment')) }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Available Seats</dt>
                        <dd class="font-semibold text-green-600">{{ number_format($classes->sum('available')) }}</dd>
                    </div>
                    <hr class="border-gray-100">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Classes Configured</dt>
                        <dd class="font-semibold text-gray-700">{{ $classes->count() }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Lock Status</dt>
                        <dd>
                            @if ($isLocked)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-semibold">🔒
                                    Locked</span>
                            @else
                                <span
                                    class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-700 font-semibold">🟡
                                    Unlocked</span>
                            @endif
                        </dd>
                    </div>
                    @if ($isLocked)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Locked By</dt>
                            <dd class="font-semibold text-gray-700 text-xs">{{ $institution->seatsLockedBy?->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Locked On</dt>
                            <dd class="font-semibold text-gray-700 text-xs">
                                {{ $institution->seats_locked_at->format('d M Y') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-xs text-blue-700 space-y-1.5">
                <p class="font-semibold text-blue-800">ℹ️ How seats work</p>
                <p>Available = Authorized Seats − Existing Enrollment − Cumulative Approved Admissions</p>
                <p>OOSC and P2P are analytics-only — they do not consume seats.</p>
                <p>Locking prevents HOI from adding/removing classes. FDE can unlock at any time.</p>
            </div>

        </div>
    </div>


    {{-- ══════════════════════════════════════════════════════════════
         UNLOCK MODAL
    ══════════════════════════════════════════════════════════════ --}}
    <div id="unlockModal" class="fixed inset-0 bg-black/40 z-50 items-center justify-center p-4" style="display:none">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">

            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800">🔓 Unlock Seat Configuration</h3>
                <button onclick="document.getElementById('unlockModal').style.display='none'"
                    class="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
            </div>

            <form method="POST" action="{{ route('fde.seats.unlock', $institution) }}">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <p class="text-sm text-gray-600">
                        Unlocking will allow the HOI to modify class setup and seat counts for
                        <strong>{{ $institution->name }}</strong>.
                    </p>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1.5">
                            Reason for Unlocking <span class="text-red-500">*</span>
                        </label>
                        <textarea name="unlock_reason" required minlength="10" maxlength="500" rows="3"
                            placeholder="e.g. HOI requested correction to seat counts after enrolment verified…"
                            class="w-full border border-gray-200 rounded-xl px-3 py-2.5 text-sm resize-none
                                         focus:outline-none focus:ring-2 focus:ring-yellow-400
                                         @error('unlock_reason') border-red-400 @enderror">{{ old('unlock_reason') }}</textarea>
                        @error('unlock_reason')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-gray-400 mt-1">Minimum 10 characters.</p>
                    </div>
                </div>
                <div class="px-6 py-4 bg-gray-50 rounded-b-2xl flex gap-3">
                    <button type="submit"
                        class="flex-1 py-2.5 bg-yellow-500 text-white rounded-xl text-sm font-semibold hover:bg-yellow-600 transition">
                        🔓 Confirm Unlock
                    </button>
                    <button type="button" onclick="document.getElementById('unlockModal').style.display='none'"
                        class="px-5 py-2.5 text-sm text-gray-500 hover:text-gray-700 rounded-xl border border-gray-200">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Auto-reopen modal if validation failed on unlock_reason --}}
    @if ($errors->has('unlock_reason'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('unlockModal').style.display = 'flex';
        });
    </script>
    @endif

    {{-- Close unlock modal on backdrop click --}}
    <script>
        document.getElementById('unlockModal').addEventListener('click', function(e) {
            if (e.target === this) this.style.display = 'none';
        });

        // Live total recalculation
        document.querySelectorAll('.seat-input').forEach(input => {
            input.addEventListener('input', () => {
                const total = [...document.querySelectorAll('.seat-input')]
                    .reduce((sum, el) => sum + (parseInt(el.value) || 0), 0);
                const display = document.getElementById('totalDisplay');
                const footer = document.getElementById('totalFooter');
                if (display) display.textContent = total.toLocaleString();
                if (footer) footer.textContent = total.toLocaleString();
            });
        });
    </script>

@endsection
