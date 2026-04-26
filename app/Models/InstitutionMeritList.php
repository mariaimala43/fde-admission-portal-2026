<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstitutionMeritList extends Model
{
    protected $fillable = [
        'institution_id',
        'title',
        'file_path',
        'original_name',
        'file_size',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function formattedSize(): string
    {
        if (! $this->file_size) return '—';
        $bytes = (int) $this->file_size;
        if ($bytes >= 1_048_576) return round($bytes / 1_048_576, 1) . ' MB';
        if ($bytes >= 1_024)     return round($bytes / 1_024, 0)     . ' KB';
        return $bytes . ' B';
    }

    public function extension(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }

    public function badgeClass(): string
    {
        return match($this->extension()) {
            'PDF'             => 'bg-red-100 text-red-700',
            'XLSX', 'XLS'     => 'bg-green-100 text-green-700',
            'CSV'             => 'bg-blue-100 text-blue-700',
            default           => 'bg-gray-100 text-gray-600',
        };
    }

    public function downloadUrl(): string
    {
        return route('merit.file', $this->id);
    }

    public function fileIcon(): string
    {
        return match($this->extension()) {
            'PDF'             => '📄',
            'XLSX', 'XLS'     => '📊',
            'CSV'             => '📋',
            default           => '📁',
        };
    }

    public function fileType(): string
    {
        return match($this->extension()) {
            'PDF'             => 'PDF Document',
            'XLSX'            => 'Excel Spreadsheet',
            'XLS'             => 'Excel Spreadsheet',
            'CSV'             => 'CSV File',
            default           => $this->extension() ?: 'File',
        };
    }

    public function isNew(): bool
    {
        return $this->created_at && $this->created_at->gte(now()->subDays(7));
    }
}
