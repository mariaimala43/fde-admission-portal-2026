{{-- SAVE AS: resources/views/admin/academic_years/form.blade.php --}}
{{-- Shared partial used by create.blade.php and edit.blade.php --}}

@if ($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-4 py-3 mb-5 text-sm">
        @foreach ($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    </div>
@endif

<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
    <div class="space-y-5">

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Year Name <span class="text-red-500">*</span>
                <span class="font-normal text-gray-400 text-xs ml-1">e.g. 2026-27</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $academicYear->name ?? '') }}" required
                maxlength="20"
                class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Start Date <span
                        class="text-red-500">*</span></label>
                <input type="date" name="start_date"
                    value="{{ old('start_date', isset($academicYear) ? $academicYear->start_date->toDateString() : '') }}"
                    required
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">End Date <span
                        class="text-red-500">*</span></label>
                <input type="date" name="end_date"
                    value="{{ old('end_date', isset($academicYear) ? $academicYear->end_date->toDateString() : '') }}"
                    required
                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <div class="border-t border-gray-100 pt-4">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-3">Admission Window</p>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Admission Opens</label>
                    <input type="date" name="admission_start"
                        value="{{ old('admission_start', isset($academicYear) ? $academicYear->admission_start?->toDateString() : '') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Admission Closes</label>
                    <input type="date" name="admission_end"
                        value="{{ old('admission_end', isset($academicYear) ? $academicYear->admission_end?->toDateString() : '') }}"
                        class="w-full border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">
                Daily Entry Cutoff Time <span class="text-red-500">*</span>
                <span class="font-normal text-gray-400 text-xs ml-1">HOI cannot edit submissions after this time</span>
            </label>
            <input type="time" name="daily_cutoff_time"
                value="{{ old('daily_cutoff_time', isset($academicYear) ? substr($academicYear->daily_cutoff_time, 0, 5) : '17:00') }}"
                required
                class="w-48 border border-gray-200 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="border-t border-gray-100 pt-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $academicYear->is_active ?? false) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                <span class="text-sm font-semibold text-gray-700">Set as Active Academic Year</span>
            </label>
            <p class="text-xs text-gray-400 mt-1 ml-7">Only one year can be active at a time. Activating this will
                deactivate all others.</p>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit"
                class="px-7 py-2.5 bg-blue-900 text-white text-sm font-bold rounded-xl hover:bg-blue-800 transition">
                {{ isset($academicYear) ? 'Update' : 'Create' }} Academic Year
            </button>
            <a href="{{ route('admin.academic-years.index') }}"
                class="px-5 py-2.5 text-sm text-gray-400 hover:text-gray-600 transition">Cancel</a>
        </div>
    </div>
</div>
