@extends('layouts.app')
@section('title', 'Import Schools')
@section('content')

    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Import Schools</h2>
        <p class="text-sm text-gray-500 mt-1">
            Upload your Excel file to import all UCs, Sectors and Institutions at once
        </p>
    </div>

    {{-- Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <h3 class="text-sm font-semibold text-blue-900 mb-3">
            File Format Requirements
        </h3>
        <p class="text-sm text-blue-800 mb-3">
            Your Excel file must have these exact column headers in row 1:
        </p>
        <div class="bg-white rounded-lg border border-blue-200 overflow-hidden mb-3">
            <table class="w-full text-sm">
                <thead class="bg-blue-900 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">uc</th>
                        <th class="px-4 py-2 text-left">sector</th>
                        <th class="px-4 py-2 text-left">school_name</th>
                        <th class="px-4 py-2 text-left">type</th>
                        <th class="px-4 py-2 text-left">gender</th>
                        <th class="px-4 py-2 text-left">shift</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="border-t border-blue-100">
                        <td class="px-4 py-2 text-gray-600">UC-1</td>
                        <td class="px-4 py-2 text-gray-600">F-8</td>
                        <td class="px-4 py-2 text-gray-600">IMCG F-8/1</td>
                        <td class="px-4 py-2 text-gray-600">I-XII</td>
                        <td class="px-4 py-2 text-gray-600">Girls</td>
                        <td class="px-4 py-2 text-gray-600">Morning</td>
                    </tr>
                    <tr class="border-t border-blue-100 bg-gray-50">
                        <td class="px-4 py-2 text-gray-600">UC-1</td>
                        <td class="px-4 py-2 text-gray-600">F-8</td>
                        <td class="px-4 py-2 text-gray-600">IMCB F-8/4</td>
                        <td class="px-4 py-2 text-gray-600">I-X</td>
                        <td class="px-4 py-2 text-gray-600">Boys</td>
                        <td class="px-4 py-2 text-gray-600">Morning</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <ul class="text-sm text-blue-800 space-y-1 list-disc list-inside">
            <li>Column headers must be lowercase exactly as shown above</li>
            <li>Type must be one of: I-V, I-VIII, I-X, I-XII, VI-VIII, VI-X, VI-XII, Model College</li>
            <li>Gender: Boys, Girls, or Co-Education</li>
            <li>Shift: Morning, Evening, or Both</li>
            <li>Duplicate school names will be skipped automatically</li>
            <li>UCs and Sectors will be created automatically if they don't exist</li>
        </ul>
    </div>

    {{-- Success --}}
    @if (session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Warning with errors --}}
    @if (session('warning'))
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded-lg mb-4 text-sm">
            {{ session('warning') }}
        </div>
    @endif

    @if (session('import_errors'))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6">
            <p class="text-sm font-semibold text-red-700 mb-2">Rows with errors:</p>
            <ul class="text-sm text-red-600 space-y-1 list-disc list-inside">
                @foreach (session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Upload Form --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 max-w-lg">
        <form method="POST" action="{{ route('admin.import.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Select Excel File
                </label>
                <input type="file" name="file" accept=".xlsx,.xls,.csv"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required />
                <p class="text-xs text-gray-400 mt-1">
                    Accepted: .xlsx, .xls, .csv — Max 10MB
                </p>
                @error('file')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                class="w-full bg-blue-900 text-white py-2.5 rounded-lg font-semibold text-sm hover:bg-blue-800 transition">
                Upload & Import
            </button>

        </form>
    </div>

@endsection
