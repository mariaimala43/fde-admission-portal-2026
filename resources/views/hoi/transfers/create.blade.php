{{-- SAVE AS: resources/views/hoi/transfers/create.blade.php --}}
@extends('layouts.app')
@section('title', 'Request Student Transfer')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Request Student Transfer</h2>
            <p class="text-sm text-gray-500 mt-1">{{ $institution->name }} → receiving school</p>
        </div>
        <a href="{{ route('hoi.transfers.index') }}"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">
            ← Back
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
            @foreach ($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">

        <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-3 mb-6 text-sm text-blue-700">
            ℹ️ Each student row becomes a <strong>separate transfer request</strong> to the same receiving school.
            Each student transfers into the <strong>same class</strong> at the receiving school.
            Enrollment updates automatically once accepted.
        </div>

        <form method="POST" action="{{ route('hoi.transfers.store') }}">
            @csrf

            {{-- Receiving School — shared for all rows --}}
            <div class="mb-6 max-w-lg">
                <label class="block text-sm font-semibold text-gray-700 mb-1">
                    Receiving School <span class="text-red-500">*</span>
                </label>
                <select name="to_institution_id" required
                    class="w-full border border-gray-200 rounded-lg px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Select receiving school —</option>
                    @foreach ($institutions as $inst)
                        <option value="{{ $inst->id }}" {{ old('to_institution_id') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}{{ $inst->code ? " ($inst->code)" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Student Rows --}}
            <div class="mb-2 flex justify-between items-center">
                <h3 class="text-sm font-bold text-gray-700">Students to Transfer</h3>
                <button type="button" id="addRowBtn"
                    class="px-4 py-1.5 text-xs font-semibold bg-blue-900 text-white rounded-lg hover:bg-blue-800 transition">
                    + Add Another Student
                </button>
            </div>

            {{-- Column Headers --}}
            <div
                class="hidden md:grid grid-cols-12 gap-2 px-3 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wide">
                <div class="col-span-3">Class <span class="text-red-400">*</span></div>
                <div class="col-span-3">Student Name <span class="font-normal normal-case text-gray-300">(optional)</span>
                </div>
                <div class="col-span-3">Father's Name <span class="font-normal normal-case text-gray-300">(optional)</span>
                </div>
                <div class="col-span-2">Notes <span class="font-normal normal-case text-gray-300">(optional)</span></div>
                <div class="col-span-1"></div>
            </div>

            {{-- Rows --}}
            <div id="studentRows" class="space-y-2 mb-6">
                <div
                    class="student-row grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-xl p-3 border border-gray-100">
                    <div class="col-span-12 md:col-span-3">
                        <select name="students[0][class_id]" required
                            class="w-full border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">— Class —</option>
                            @foreach ($myClasses as $ic)
                                <option value="{{ $ic->class_id }}">{{ $ic->classModel->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-12 md:col-span-3">
                        <input type="text" name="students[0][student_name]" placeholder="e.g. Ahmed Ali"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-12 md:col-span-3">
                        <input type="text" name="students[0][father_name]" placeholder="e.g. Muhammad Ali"
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-12 md:col-span-2">
                        <input type="text" name="students[0][notes]" placeholder="Reason..."
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="col-span-12 md:col-span-1 flex justify-center">
                        <button type="button" onclick="removeRow(this)"
                            class="remove-btn text-red-400 hover:text-red-600 text-xl font-bold w-8 h-8 rounded-lg hover:bg-red-50 transition hidden">✕</button>
                    </div>
                </div>
            </div>

            <div class="flex gap-3">
                <button type="submit"
                    class="px-7 py-2.5 bg-blue-900 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition">
                    Submit Transfer Request(s)
                </button>
                <a href="{{ route('hoi.transfers.index') }}"
                    class="px-5 py-2.5 text-sm text-gray-400 hover:text-gray-600 transition">Cancel</a>
            </div>

        </form>
    </div>

    {{-- Pass class options to JS --}}
    <script>
        const classOptions = @json($myClasses->map(fn($ic) => ['id' => $ic->class_id, 'name' => $ic->classModel->name]));
    </script>

@endsection

@push('scripts')
    <script>
        let rowCount = 1;

        function buildOptions() {
            return classOptions.map(c => `<option value="${c.id}">${c.name}</option>`).join('');
        }

        function updateRemoveBtns() {
            const rows = document.querySelectorAll('.student-row');
            rows.forEach(r => {
                r.querySelector('.remove-btn').classList.toggle('hidden', rows.length === 1);
            });
        }

        function removeRow(btn) {
            btn.closest('.student-row').remove();
            // Re-index all rows
            document.querySelectorAll('.student-row').forEach((row, i) => {
                row.querySelectorAll('[name]').forEach(el => {
                    el.name = el.name.replace(/students\[\d+\]/, `students[${i}]`);
                });
            });
            rowCount = document.querySelectorAll('.student-row').length;
            updateRemoveBtns();
        }

        document.getElementById('addRowBtn').addEventListener('click', () => {
            const div = document.createElement('div');
            div.className =
                'student-row grid grid-cols-12 gap-2 items-center bg-gray-50 rounded-xl p-3 border border-gray-100';
            div.innerHTML = `
            <div class="col-span-12 md:col-span-3">
                <select name="students[${rowCount}][class_id]" required
                    class="w-full border border-gray-200 bg-white rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Class —</option>
                    ${buildOptions()}
                </select>
            </div>
            <div class="col-span-12 md:col-span-3">
                <input type="text" name="students[${rowCount}][student_name]" placeholder="e.g. Ahmed Ali"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-12 md:col-span-3">
                <input type="text" name="students[${rowCount}][father_name]" placeholder="e.g. Muhammad Ali"
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-12 md:col-span-2">
                <input type="text" name="students[${rowCount}][notes]" placeholder="Reason..."
                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="col-span-12 md:col-span-1 flex justify-center">
                <button type="button" onclick="removeRow(this)"
                    class="remove-btn text-red-400 hover:text-red-600 text-xl font-bold w-8 h-8 rounded-lg hover:bg-red-50 transition">✕</button>
            </div>
        `;
            document.getElementById('studentRows').appendChild(div);
            rowCount++;
            updateRemoveBtns();
        });

        updateRemoveBtns();
    </script>
@endpush
