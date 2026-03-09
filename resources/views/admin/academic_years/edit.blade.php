{{-- SAVE AS: resources/views/admin/academic_years/edit.blade.php --}}
@extends('layouts.app')
@section('title', 'Edit Academic Year')
@section('content')
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Edit — {{ $academicYear->name }}</h2>
        <a href="{{ route('admin.academic-years.index') }}"
            class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg transition">←
            Back</a>
    </div>
    <form method="POST" action="{{ route('admin.academic-years.update', $academicYear) }}">
        @csrf
        @method('PUT')
        @include('admin.academic_years.form')
    </form>
@endsection
